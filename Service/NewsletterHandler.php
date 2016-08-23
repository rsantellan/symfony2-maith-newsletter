<?php

namespace Maith\NewsletterBundle\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Maith\NewsletterBundle\Entity\User;
use Maith\NewsletterBundle\Entity\UserGroup;
use Maith\NewsletterBundle\Entity\Content;
use Maith\NewsletterBundle\Entity\ContentSend;
use Maith\NewsletterBundle\Entity\EmailLayout;

class NewsletterHandler
{
    protected $em;

    protected $logger;

    public function __construct(EntityManager $em, Logger $logger)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->logger->addDebug('Starting Newsletter manager');
    }

    public function retrieveNewsletterUsersQuantity()
    {
        $quantitySql = 'select count(id) as quantity from maith_newsletter_user where active = 1';
        $stmt = $this->em->getConnection()->executeQuery($quantitySql);
        $row = $stmt->fetch();

        return $row['quantity'];
    }

    public function retrieveAllNewsletterGroups()
    {
        return $this->em->getRepository('MaithNewsletterBundle:UserGroup')->findAll();
    }

    public function retrieveAllEmailsLayouts($objects = false)
    {
        if (!$objects) {
            $query = $this->em->createQuery('select e.id, e.name from MaithNewsletterBundle:EmailLayout e order by e.name');

            return $query->getResult();
        } else {
            return $this->em->getRepository('MaithNewsletterBundle:EmailLayout')->findAll();
        }
    }

    public function retrieveCreatedContents($offset = 0, $limit = 10)
    {
        $query = $this->em->createQuery('select c from MaithNewsletterBundle:Content c where c.active = true order by c.createdat desc')
                          ->setFirstResult($offset)
                          ->setMaxResults($limit);

        $result = $query->getResult();
        $data = array();
        foreach ($result as $content) {
            $aux = array(
              'title' => $content->getTitle(),
              'id' => $content->getId(),
              'created' => 0,
              'sended' => 0,
            );
            foreach ($content->getContentSend() as $sended) {
                $aux['created'] = $aux['created'] + 1;
                if ($sended->getSended()) {
                    $aux['sended'] = $aux['sended'] + 1;
                }
            }
            $data[$content->getId()] = $aux;
        }

        return $data;
    }

    public function saveNewsletterUser(User $user)
    {
        $responseMessage = '';
        try {
            $this->em->persist($user);
            $this->em->flush();
            $responseMessage = 'Datos guardados con exito';
        } catch (\Exception $e) {
            $this->logger->error($e);
            $responseMessage = 'El email ya existe.';
        }

        return $responseMessage;
    }

    public function saveNewsletterGroup(UserGroup $userGroup)
    {
        try {
            $this->em->persist($userGroup);
            $this->em->flush();

            return $userGroup;
        } catch (\Exception $e) {
            $this->logger->error($e);

            return;
        }
    }

    public function retrieveNewsletterContent($id)
    {
        return $this->em->getRepository('MaithNewsletterBundle:Content')->find($id);
    }

    public function retrieveNewsletterContentSend($id)
    {
        return $this->em->getRepository('MaithNewsletterBundle:ContentSend')->find($id);
    }

    public function retrieveNewsletterLayout($id)
    {
        return $this->em->getRepository('MaithNewsletterBundle:EmailLayout')->find($id);
    }

    public function retrieveUserGroup($id)
    {
        return $this->em->getRepository('MaithNewsletterBundle:UserGroup')->find($id);
    }

    public function persistContent(Content $content)
    {
        $this->em->persist($content);
        $this->em->flush();

        return $content;
    }

    public function persistContentSend(ContentSend $contentSend)
    {
        $this->em->persist($contentSend);
        $this->em->flush();

        return $contentSend;
    }

    public function persistEmailLayout(EmailLayout $emailLayout)
    {
        $this->em->persist($emailLayout);
        $this->em->flush();

        return $emailLayout;
    }

    public function persistUserGroup(UserGroup $userGroup)
    {
        $this->em->persist($userGroup);
        $this->em->flush();

        return $userGroup;
    }

    public function retrieveUserSqlCursor()
    {
        $usersSql = 'select email, active from maith_newsletter_user';
        $results = $this->em->getConnection()->query($usersSql);

        return $results;
    }

    public function retrieveSendedUserSqlCursor($identifier)
    {
        $usersSql = 'select u.email, su.hits from maith_newsletter_user u left join maith_newsletter_content_send_user su on su.maith_newsletter_user_id = u.id where su.maith_newsletter_content_send_id = ? ';
        $results = $this->em->getConnection()->executeQuery($usersSql, array($identifier), array(\PDO::PARAM_INT));

        return $results;
    }

    public function saveNewsletterUserCsvFile($filePath)
    {
        $openHandler = fopen($filePath, 'r');
        $insertSql = 'INSERT INTO maith_newsletter_user (email, active ) VALUES (:email, :active)';
        $stmtInsert = $this->em->getConnection()->prepare($insertSql);

        while (!feof($openHandler)) {
            $row = fgetcsv($openHandler);
            if ($row[0] != null) {
                try {
                    $stmtInsert->bindValue('email', $row[0]);
                    $stmtInsert->bindValue('active', 1);
                    $stmtInsert->execute();
                    ++$counter;
                } catch (\Exception $e) {
                }
            }
        }
        fclose($openHandler);
    }

    public function retrieveContentLayoutBody($emailLayoutId)
    {
        $sql = 'select id, body from maith_newsletter_email_layout where id = :id';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->execute(array('id' => $emailLayoutId));
        $row = $stmt->fetch();

        return $row['body'];
    }

    public function sendContentToAll($contentSendId)
    {
        $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1';
        $stmt = $this->em->getConnection()->prepare($sql);
        $stmt->bindValue('contentId', $contentSendId);
        $stmt->execute();
    }

    public function sendContentToUsers($contentSendId, $usersList, $usersIdsList)
    {
        $sqlSelectIds = 'select id from maith_newsletter_user where active = 1 and email = :email';
        $emailsIds = array();
        foreach ($usersList as $email) {
            $trimmedEmail = trim($email);
            if (!empty($trimmedEmail)) {
                $resultsEmail = $this->em->getConnection()->executeQuery($sqlSelectIds, array('email' => $trimmedEmail));
                $row = $resultsEmail->fetch();
                if (isset($row['id'])) {
                    $emailsIds[$row['id']] = $row['id'];
                }
            }
        }
        foreach ($usersIdsList as $id) {
            $emailsIds[$id] = $id;
        }
        $sqlIds = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and id = :id';
        $stmtIds = $this->em->getConnection()->prepare($sqlIds);
        foreach ($emailsIds as $userId) {
            $stmtIds->bindValue('contentId', $contentSendId);
            $stmtIds->bindValue('id', $userId);
            $stmtIds->execute();
        }
    }

    public function sendContentToGroups($contentSendId, $groupsList)
    {
        $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and id in (select user_id from maith_newsletter_users_groups where user_group_id = :groupId )';
        $stmt = $this->em->getConnection()->prepare($sql);
        foreach ($groupsList as $groupId) {
            if ($groupId !== '') {
                $stmt->bindValue('contentId', $contentSendId);
                $stmt->bindValue('groupId', $groupId);
                $stmt->execute();
            }
        }
    }

    public function retrieveUsersToSendList($groupUsersLimit = 50)
    {
        $groupData = array();
        $groupsSql = 'select g.id, g.name from maith_newsletter_group g order by g.name asc';
        $resultGroups = $this->em->getConnection()->executeQuery($groupsSql);
        $usersSql = 'select u.id, u.email from maith_newsletter_user u left join maith_newsletter_users_groups ug on ug.user_id = u.id where u.active = 1 and ug.user_group_id = :groupId limit '.$groupUsersLimit;
        while ($groupRow = $resultGroups->fetch()) {
            $groupData[$groupRow['name']] = array();
            $resultUsers = $this->em->getConnection()->executeQuery($usersSql, array('groupId' => $groupRow['id']));
            while ($userRow = $resultUsers->fetch()) {
                $groupData[$groupRow['name']][] = array('identifier' => $userRow['id'], 'label' => $userRow['email']);//[$userRow['id']] = $userRow['email'];
            }
        }

        return $groupData;
    }

    public function retrieveUserForSearch($search)
    {
        $term = '%'.$search.'%';
        $usersSearchSql = 'select id, email, active from maith_newsletter_user where email LIKE ? and active = 1 limit 20';
        $results = $this->em->getConnection()->executeQuery($usersSearchSql, array($term), array(\PDO::PARAM_STR));
        $returnData = array();
        while ($row = $results->fetch()) {
            $returnData[] = array('id' => $row['id'], 'label' => $row['email']);
        }

        return $returnData;
    }

    public function retrieveUserSearchWithLimit($search)
    {
        $limit = 50;
        $term = '%'.$search.'%';
        $usersSearchSql = 'select id, email, active from maith_newsletter_user where email LIKE ? order by email limit '.$limit;
        $results = $this->em->getConnection()->executeQuery($usersSearchSql, array($term), array(\PDO::PARAM_STR));
        $list = array();
        while ($row = $results->fetch()) {
            $list[] = array('id' => $row['id'], 'email' => $row['email'], 'active' => $row['active']);
        }

        return $list;
    }

    public function removeContentSend($id)
    {
        $contentSend = $this->retrieveNewsletterContentSend($id);
        if (!$contentSend) {
            throw new \Exception('Objecto no encontrado');
        }
        $this->em->remove($contentSend);
        $this->em->flush();

        return true;
    }

    public function removeUserGroup($id)
    {
        $userGroup = $this->retrieveUserGroup($id);
        if (!$userGroup) {
            throw new \Exception('Objecto no encontrado');
        }
        $this->em->remove($userGroup);
        $this->em->flush();

        return true;
    }

    public function removeUserOfGroup($userId, $groupId)
    {
        $group = $this->retrieveUserGroup($groupId);
        $user = $this->em->getRepository('MaithNewsletterBundle:User')->find($userId);
        if (!$group) {
            throw new \Exception('Grupo no encontrado');
        }
        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }
        $user->removeUserGroup($group);
        $group->removeUser($user);
        $this->em->persist($user);
        $this->em->persist($group);
        $this->em->flush();
    }

    public function addUserToGroup($groupId, $userList)
    {
        $group = $this->retrieveUserGroup($groupId);
        if (!$group) {
            throw new \Exception('Grupo no encontrado');
        }
        $insertedUsersList = array();
        foreach ($userList as $email) {
            $trimmedEmail = trim($email);
            if (!empty($trimmedEmail)) {
                $user = $this->em->getRepository('MaithNewsletterBundle:User')->findOneBy(
                array('email' => $trimmedEmail)
            );
                if ($user) {
                    try {
                        $user->addUserGroup($group);
                        $group->addUser($user);
                        $this->em->persist($user);
                        $this->em->persist($group);
                        $this->em->flush();
                        $insertedUsersList[] = $user;
                    } catch (\Exception $ex) {
                        $this->logger->error($ex);
                    }
                }
            }
        }

        return $insertedUsersList;
    }

    public function changeActiveUserStatue($userId, $active)
    {
        $user = $this->em->getRepository('MaithNewsletterBundle:User')->find($userId);
        if (!$user) {
            throw new \Exception('Usuario no encontrado');
        }
        $user->setActive($active);
        $this->em->persist($user);
        $this->em->flush();

        return $user;
    }
}
