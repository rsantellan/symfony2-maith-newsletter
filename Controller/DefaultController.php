<?php

namespace Maith\NewsletterBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\StreamedResponse;

use JMS\SecurityExtraBundle\Annotation\Secure;
use JMS\SecurityExtraBundle\Annotation\PreAuthorize;

use Maith\NewsletterBundle\Entity\User;
use Maith\NewsletterBundle\Entity\UserGroup;
use Maith\NewsletterBundle\Entity\Content;
use Maith\NewsletterBundle\Entity\ContentSend;
use Maith\NewsletterBundle\Entity\EmailLayout;

use Maith\NewsletterBundle\Form\UserType;
use Maith\NewsletterBundle\Form\UserGroupType;
use Maith\NewsletterBundle\Form\ContentType;
use Maith\NewsletterBundle\Form\ContentEditType;
use Maith\NewsletterBundle\Form\ContentSendType;
use Maith\NewsletterBundle\Form\EmailLayoutType;

/**
 * 
 * @PreAuthorize("hasRole('ROLE_MANAGE_NEWSLETTER')")
 */
class DefaultController extends Controller
{
  
    private $limitContent = 10;
  
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();

        $quantitySql = "select count(id) as quantity from maith_newsletter_user where active = 1"; 
        $stmt = $em->getConnection()->executeQuery($quantitySql);
        $row = $stmt->fetch();
        $quantity = $row['quantity'];
        $groups = $em->getRepository('MaithNewsletterBundle:UserGroup')->findAll();
        
        $query = $em->createQuery("select e.id, e.name from MaithNewsletterBundle:EmailLayout e order by e.name");
      
        $emailLayouts = $query->getResult();
        
        // Forms
        $createUserForm = $this->createNewNewsletterUserForm(new User());
        $createGroupForm = $this->createNewNewsletterGroupForm(new UserGroup());
        $uploadUsersForm = $this->uploadUsersForm();
        return $this->render('MaithNewsletterBundle:Default:index.html.twig', array(
            'smallnavigation' => true,
            'userform' => $createUserForm->createView(),
            'uploadusers' => $uploadUsersForm->createView(),
            'groupform' => $createGroupForm->createView(),
            'groups' => $groups,
            'quantity' => $quantity,
            'contents' => $this->retrieveCreatedContents(0, $this->limitContent),
            'limitContent' => $this->limitContent,
            'activemenu' => 'newsletter',
            'pager' => 0,
            'emailLayouts' => $emailLayouts,
        ));
    }
    
    public function changeContentPageAction(Request $request, $page)
    {
        $responseData = array(
            'result' => true,
            'html' => '',
        );
        $offset = ($page * $this->limitContent);// + $this->limitContent;
        //'contents' : contents, 'pager': 0, 'limitContent' : limitContent
        $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:_contentTable.html.twig', array(
          'contents' => $this->retrieveCreatedContents($offset, $this->limitContent),
          'pager' => $page,
          'limitContent' => $this->limitContent,
        ));
        
        
        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);
        return $returnJson;
    }
    
    private function retrieveCreatedContents($offset = 0, $limit = 10)
    {
      $em = $this->getDoctrine()->getManager();
      $query = $em->createQuery("select c from MaithNewsletterBundle:Content c where c.active = true order by c.createdat desc")
                          ->setFirstResult($offset)
                          ->setMaxResults($limit);
      
      $result = $query->getResult();
      $data = array();
      foreach($result as $content)
      {
          $aux = array(
              'title' => $content->getTitle(), 
              'id' => $content->getId(),
              'created' => 0,
              'sended' => 0,
            );
          foreach($content->getContentSend() as $sended)
          {
              $aux['created'] = $aux['created'] + 1;
              if($sended->getSended())
              {
                  $aux['sended'] = $aux['sended'] + 1;
              }
          }
          $data[$content->getId()] = $aux;
      }
      return $data;
    }
    
    private function uploadUsersForm()
    {
        $form = $this->createFormBuilder()
            ->add('submitFile', 'file', array('label' => 'File to Submit'))
            ->getForm();
        return $form;
    }
    
    private function createNewNewsletterUserForm(User $entity)
    {
        $form = $this->createForm(new UserType(), $entity, array(
            'method' => 'POST',
        ));

        return $form;
    }
    
    private function createNewNewsletterGroupForm(UserGroup $entity)
    {
        $form = $this->createForm(new UserGroupType(), $entity, array(
            'method' => 'POST',
        ));

        return $form;
    }
    
    private function createNewNewsletterContentForm(Content $entity)
    {
        $form = $this->createForm(new ContentType(), $entity, array(
            'method' => 'POST',
        ));

        return $form;
    }
    
    private function createNewEmailLayoutForm(EmailLayout $entity)
    {
        $form = $this->createForm(new EmailLayoutType(), $entity, array(
            'method' => 'POST',
        ));

        return $form;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_ADD_USER")
     */
    public function createSimpleUserAction(Request $request)
    {
        $entity = new User();
        $form = $this->createNewNewsletterUserForm($entity);
        $form->handleRequest($request);

        $responseData = array(
            'result' => false,
            'message' => 'Error en el formulario',
            'html' => '',
        );
        if ($form->isValid()) {
            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                $responseData['message'] = 'Datos guardados con exito';
            }catch(\Exception $e)
            {
                $responseData['message'] = 'El email ya existe.';
            }
            $createUserForm = $this->createNewNewsletterUserForm(new User());
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:userForm.html.twig', array(
              'userform' => $createUserForm->createView(),
            ));
            
            $responseData['result'] = true;
        }else{
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:userForm.html.twig', array(
              'userform' => $form->createView(),
            ));
        }
        
        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);
        return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_ADD_GROUP")
     */
    public function createGroupAction(Request $request)
    {
        $entity = new UserGroup();
        $form = $this->createNewNewsletterGroupForm($entity);
        $form->handleRequest($request);

        $responseData = array(
            'result' => false,
            'message' => 'Error en el formulario',
            'html' => '',
            'listhtml' => '',
        );
        if ($form->isValid()) {
            try{
                $em = $this->getDoctrine()->getManager();
                $em->persist($entity);
                $em->flush();
                $responseData['message'] = 'Datos guardados con exito';
                $responseData['listhtml'] = $this->renderView('MaithNewsletterBundle:Default:_groupRow.html.twig', array(
                    'group' => $entity
                ));
            }catch(\Exception $e)
            {
                $responseData['message'] = 'El grupo ya existe.';
            }
            $createGroupForm = $this->createNewNewsletterGroupForm(new UserGroup());
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:groupForm.html.twig', array(
              'groupform' => $createGroupForm->createView(),
            ));
            
            $responseData['result'] = true;
        }else{
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:groupForm.html.twig', array(
              'groupform' => $form->createView(),
            ));
        }
        
        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);
        return $returnJson;
    }
    
    /**
     * 
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_ADD_CONTENT")
     */
    public function createContentAction()
    {
      $responseData = array(
            'result' => true,
            'message' => 'Creando contenido',
            'html' => 'adsasdsa',
        );
      
      $form = $this->createNewNewsletterContentForm(new Content());
      
      $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:composeForm.html.twig', array(
              'form' => $form->createView(),
            ));
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
      
    }
    
    /**
     * 
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function editContentAction($id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:Content')->find($id);

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Content entity.');
      }
      
      $form = $this->createForm(new ContentEditType(), $entity, array(
            'method' => 'POST',
        ));
      
      $responseData['html'] = $this->createEditContent($entity, $form);
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    private function createEditContent($entity, $form){
      $formSend = $this->createForm(new ContentSendType(), new ContentSend(), array(
          'method' => 'POST',
      ));
      $em = $this->getDoctrine()->getManager();
      $emailLayouts = $em->getRepository('MaithNewsletterBundle:EmailLayout')->findAll();
      
      return $this->renderView('MaithNewsletterBundle:Default:editForm.html.twig', array(
              'form' => $form->createView(),
              'formSend' => $formSend->createView(),
              'entity' => $entity,
              'emailLayouts' => $emailLayouts,
            ));
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function updateContentAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:Content')->find($id);

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Content entity.');
      }
      $form = $this->createForm(new ContentEditType(), $entity, array(
            'method' => 'POST',
        ));
      $form->handleRequest($request);

      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
       if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->createEditContent($entity, $form);
            $responseData['result'] = true;
        }
        
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_ADD_CONTENT")
     */
    public function saveContentAction(Request $request)
    {
      $entity = new Content();
      $form = $this->createNewNewsletterContentForm($entity);
      $form->handleRequest($request);

      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
       if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->createEditContent($entity, $form);
            $aux = array(
              'title' => $entity->getTitle(), 
              'id' => $entity->getId(),
              'created' => 0,
              'sended' => 0,
            );
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_contentTableRow.html.twig', array(
                'content' => $aux
            ));
            $responseData['result'] = true;
        }else{
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:composeForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }
        
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @return StreamedResponse
     * @Secure(roles="ROLE_NEWSLETTER_DOWNLOAD_USERS")
     */
    public function downloadUsersAction()
    {
        $response = new StreamedResponse();
        $em = $this->getDoctrine()->getManager();
        $response->setCallback(function() use ($em) {
     
            $handle = fopen('php://output', 'w+');
     
            // Add a row with the names of the columns for the CSV file
            fputcsv($handle, array('Email', 'Active'),';');
            // Query data from database
            
            $usersSql = "select email, active from maith_newsletter_user"; 
            $results = $em->getConnection()->query( $usersSql );
            // Add the data queried from database
            while( $row = $results->fetch() )
            {
                fputcsv($handle,array($row['email'],
                    $row['active']                    
                ),';');
            }
     
            fclose($handle);
        });
        
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename="export.csv"');
     
        return $response;
    }
    
    /**
     * 
     * @param Request $request
     * @return type
     * @Secure(roles="ROLE_NEWSLETTER_UPLOAD_USERS")
     */
    public function uploadUsersFileAction(Request $request)
    {
        $uploadUsersForm = $this->uploadUsersForm();
        $uploadUsersForm->handleRequest($request);
        $counter = 0;
        // If form is valid
        if ($uploadUsersForm->isValid()) {
             // Get file
             $file = $uploadUsersForm->get('submitFile');
             // Your csv file here when you hit submit button
             $openHandler = fopen($file->getData()->getPathname(), "r"); 
             //print_r(fgetcsv($openHandler));
             $em = $this->getDoctrine()->getManager();
             //$usersSql = "select email, active from maith_newsletter_user"; 
             $insertSql = 'INSERT INTO maith_newsletter_user (email, active ) VALUES (:email, :active)';
             $stmtInsert = $em->getConnection()->prepare( $insertSql );
             
             while (!feof($openHandler) ) {
               $row = fgetcsv($openHandler);
               if($row[0] != NULL)
               {
                 try{
                    $stmtInsert->bindValue('email', $row[0]);
                    $stmtInsert->bindValue('active', 1);
                    $stmtInsert->execute();
                    $counter++;
                  }catch(\Exception $e)
                  {
                  }
               }
               
             }
             fclose($openHandler); 
        }
        $this->get('session')->getFlashBag()->add('notif-success', sprintf('Se agregaron %s contactos', $counter));
        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return type
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function previewContentAction(Request $request, $id)
    {
      $layout = $request->get('layout', null);
      $entityLayout = null;
      
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:Content')->find($id);
      if($layout)
      {
        $entityLayout = $em->getRepository('MaithNewsletterBundle:EmailLayout')->find($layout);
      }
      

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Content entity.');
      }
      if($layout && !$entityLayout){
        throw $this->createNotFoundException('Unable to find Email layout entity.');
      }
      $newBody= '';
      if($layout)
      {
        $dbBody = $this->retrieveEmailLayoutBody($layout, $em);
        $newBody = str_replace('[[body]]', $entity->getBody(), $dbBody);
        
      }
      else
      {
        $newBody = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterBody.html.twig', array(
            'entity' => $entity,
            ));
      }
      return $this->render('MaithNewsletterBundle:Default:baseNewsletterHeader.html.twig', array(
          'bodycontent' => $newBody,
          ));

    }
    
    /**
     * 
     * @param type $id
     * @return type
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function previewSendContentAction($id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:ContentSend')->find($id);

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Content Sended entity.');
      }
      return $this->render('MaithNewsletterBundle:Default:contentSend.html.twig', array(
            'entity' => $entity,
            'body' => stream_get_contents($entity->getBody()),
            ));
    }
    
    /**
     * 
     * @param type $id
     * @return StreamedResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function downloadSendedUsersAction($id)
    {
        $response = new StreamedResponse();
        $em = $this->getDoctrine()->getManager();
        $response->setCallback(function() use ($em, $id) {
     
            $handle = fopen('php://output', 'w+');
     
            // Add a row with the names of the columns for the CSV file
            fputcsv($handle, array('Email', 'Hits'),';');
            // Query data from database
            
            $usersSql = "select u.email, su.hits from maith_newsletter_user u left join maith_newsletter_content_send_user su on su.maith_newsletter_user_id = u.id where su.maith_newsletter_content_send_id = ? "; 
            $results = $em->getConnection()->executeQuery( $usersSql, array($id), array(\PDO::PARAM_INT) );
            // Add the data queried from database
            while( $row = $results->fetch() )
            {
                fputcsv($handle,array($row['email'],
                    $row['hits']                    
                ),';');
            }
     
            fclose($handle);
        });
        
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition','attachment; filename="export.csv"');
     
        return $response;
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function sendContentQueueAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();

      $content = $em->getRepository('MaithNewsletterBundle:Content')->find($id);
      if (!$content) {
          throw $this->createNotFoundException('Unable to find Content entity.');
      }
      $entity = new ContentSend();
      $entity->setContent($content);
      $entity->setTitle($content->getTitle());
      $body = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterBody.html.twig', array(
            'entity' => $content,
            ));
      $fullBody = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterHeader.html.twig', array(
            'bodycontent' => $body,
            ));
      $entity->setBody($fullBody);
      
      $form = $this->createForm(new ContentSendType(), $entity, array(
          'method' => 'POST',
      ));
      
      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
      
      $form->handleRequest($request);
      $emailLayout = $entity->getEmailLayout();
      if($emailLayout)
      {
        $sql = "select id, body from maith_newsletter_email_layout where id = :id";
        $stmt = $em->getConnection()->prepare( $sql );
        $stmt->execute(array('id' => $emailLayout->getId()));
        $row = $stmt->fetch();
        $newBody = str_replace('[[body]]', $content->getBody(), $row['body']);
        $fullBody = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterHeader.html.twig', array(
            'bodycontent' => $newBody,
            ));
        $entity->setBody($fullBody);
      }
      
      if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $sendToType = $form->get('sendToType')->getData();
            $sendList = $form->get('sendlist')->getData();
            $sendlistIds = $form->get('sendlistIds')->getData();
          switch ($sendToType) {
            case 2:
              $explodedList = explode(",", $sendList);
              $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and id in (select user_id from maith_newsletter_users_groups where usergroup_id = :groupId )';
              $stmt = $em->getConnection()->prepare( $sql );
              foreach($explodedList as $groupId)
              {
                if($groupId !== "")
                {
                  $stmt->bindValue('contentId', $entity->getId());
                  $stmt->bindValue('groupId', $groupId);
                  $stmt->execute();    
                }
                    
              }
              
              break;
            case 3:
              $explodedList = explode(",", $sendList);
              $sqlSelectIds = 'select id from maith_newsletter_user where active = 1 and email = :email';
              $emailsIds = array();
              foreach($explodedList as $email){
                $trimmedEmail = trim($email);
                if(!empty($trimmedEmail))
                {
                  $resultsEmail = $em->getConnection()->executeQuery( $sqlSelectIds, array('email' => $trimmedEmail) );
                  $row = $resultsEmail->fetch();
                  if(isset($row['id']))
                  {
                    $emailsIds[$row['id']] = $row['id'];
                  }
                }
              }
              $explodedListIds = explode(',', $sendlistIds);
              foreach($explodedListIds as $id){
                $emailsIds[$id] = $id;
              }
              $sqlIds = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and id = :id';
              $stmtIds = $em->getConnection()->prepare( $sqlIds );
              foreach($emailsIds as $userId){
                $stmtIds->bindValue('contentId', $entity->getId());
                $stmtIds->bindValue('id', $userId);
                $stmtIds->execute();
              }
              break;
            default:
              $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1';
              $stmt = $em->getConnection()->prepare( $sql );
              $stmt->bindValue('contentId', $entity->getId());
              $stmt->execute();
              break;
          }
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:sendedRow.html.twig', array(
              'sended' => $entity,
            ));
            $responseData['result'] = true;
        }else{
            //$responseData['html'] = $this->createEditContent($entity, $form);
        }
        
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function retrieveGroupsAction(){
      $em = $this->getDoctrine()->getManager();
      $groups = $em->getRepository('MaithNewsletterBundle:UserGroup')->findAll();
      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => $this->renderView('MaithNewsletterBundle:Default:groupsSelector.html.twig', array(
              'groups' => $groups,
            )),
      );
      
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function retrieveUserFormAction()
    {
      $showGroups = true;
      $groupUsersLimit = 50;
      $groupData = array();
      if($showGroups)
      {
          $em = $this->getDoctrine()->getManager();
          $groupsSql = 'select g.id, g.name from maith_newsletter_group g order by g.name asc';
          $resultGroups = $em->getConnection()->executeQuery( $groupsSql);
          $usersSql = 'select u.id, u.email from maith_newsletter_user u left join maith_newsletter_users_groups ug on ug.user_id = u.id where u.active = 1 and ug.usergroup_id = :groupId limit '.$groupUsersLimit;
          while( $groupRow = $resultGroups->fetch() )
          {
              $groupData[$groupRow['name']] = array();
              $resultUsers = $em->getConnection()->executeQuery( $usersSql, array('groupId' => $groupRow['id']));
              while( $userRow = $resultUsers->fetch() )
              {
                  $groupData[$groupRow['name']][] = array('identifier' => $userRow['id'], 'label' => $userRow['email']);//[$userRow['id']] = $userRow['email'];
              }
          }
          
      }
      $rowClass = '';
      $perRow = 0;
      switch (count($groupData)) {
          case 0:
              $rowClass = '';
              break;
          case 1:
              $rowClass = 'col-lg-12';
              $perRow = 1;
              break;
          case 2:
              $rowClass = 'col-lg-6';
              $perRow = 2;
              break;
          case 3:
              $rowClass = 'col-lg-4';
              $perRow = 3;
              break;
          default:
              $rowClass = 'col-lg-3';
              $perRow = 4;
              break;
      }
      
      
      $responseData = array(
          'result' => true,
          'message' => 'Error en el formulario',
          'html' => $this->renderView('MaithNewsletterBundle:Default:usersSelector.html.twig', array(
              'groupData' => $groupData,
              'rowClass' => $rowClass,
              'perRow' => $perRow,
            )),
      );
      
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function retrieveUsersAction(Request $request)
    {
      $term = '%'.$request->get('term').'%';
      $em = $this->getDoctrine()->getManager();
      $usersSearchSql = "select id, email, active from maith_newsletter_user where email LIKE ? and active = 1 limit 20"; 
      $results = $em->getConnection()->executeQuery( $usersSearchSql, array($term), array(\PDO::PARAM_STR) );
      // Add the data queried from database
      $returnData = array();
      while( $row = $results->fetch() )
      {
          $returnData[] = array('id' => $row['id'], 'label' => $row['email']);
      }
      $returnJson = new JsonResponse();
      $returnJson->setData($returnData);
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_SEARCH_USER")
     */
    public function searchListUsersAction(Request $request)
    {
      $limit = 50;
      $search = $request->get('search');
      $term = '%'.$search.'%';
      $em = $this->getDoctrine()->getManager();
      $usersSearchSql = "select id, email, active from maith_newsletter_user where email LIKE ? order by email limit ".$limit; 
      $results = $em->getConnection()->executeQuery( $usersSearchSql, array($term), array(\PDO::PARAM_STR) );
      // Add the data queried from database
      $list = array();
      while( $row = $results->fetch() )
      {
          $list[] = array('id' => $row['id'], 'email' => $row['email'], 'active' => $row['active']);
      }
      $html = $this->renderView('MaithNewsletterBundle:Default:showUsersList.html.twig', array(
                    'list' => $list,
                    'search' => $search,
                    'limit' => $limit,
                    'limitReached' => $limit == count($list),
                ));
      $returnJson = new JsonResponse();
      $returnJson->setData(array(
          'result' => true,
          'html' => $html,
      ));
      return $returnJson;
    }
    
    /**
     * 
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function removeSendedContentAction($id)
    {
      $em = $this->getDoctrine()->getManager();

      $content = $em->getRepository('MaithNewsletterBundle:ContentSend')->find($id);
      if (!$content) {
          throw $this->createNotFoundException('Unable to find Content Send entity.');
      }
      $message = 'Envio eliminado correctamente';
      $result = true;
      try
      {
        $em->remove($content);
        $em->flush();
      }catch(\Exception $e)
      {
        $message = $e->getMessage();
        $result = false;
      }
      
      
      $returnJson = new JsonResponse();
      $returnJson->setData(array(
          'id' => $id,
          'message' => $message,
          'result' => $result,
      ));
      return $returnJson;
    }
    
    /**
     * 
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_CREATE_EMAIL_LAYOUT")
     */
    public function createEmailLayoutAction()
    {
      $content = new ContentSend();
      $content->setBody('[[body]]');
      $content->setTitle('[[title]]');
      $body = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterBody.html.twig', array(
            'entity' => $content,
            ));
      
      $default = new EmailLayout();
      $default->setBody($body);
      $form = $this->createNewEmailLayoutForm($default);
      $html = $this->renderView('MaithNewsletterBundle:Default:emailLayoutForm.html.twig', array(
              'form' => $form->createView(),
            ));
      $returnJson = new JsonResponse();
      $returnJson->setData(array(
          'id' => 1,
          'html' => $html,
          'result' => true,
      ));
      return $returnJson;
    }
    
    private function retrieveEmailLayoutBody($id, $em)
    {
        $sql = "select id, body from maith_newsletter_email_layout where id = :id";
        $stmt = $em->getConnection()->prepare( $sql );
        $stmt->execute(array('id' => $id));
        $row = $stmt->fetch();
        return $row['body'];
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_EMAIL_LAYOUT")
     */
    public function editEmailLayoutAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:EmailLayout')->find($id);
      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Email Layout entity.');
      }
      
      $default = new EmailLayout();
      $default->setBody($this->retrieveEmailLayoutBody($id, $em));
      $default->setName($entity->getName());
      $form = $this->createNewEmailLayoutForm($default);
      $html = $this->renderView('MaithNewsletterBundle:Default:emailLayoutUpdateForm.html.twig', array(
              'form' => $form->createView(),
              'entity' => $entity,
            ));
      $returnJson = new JsonResponse();
      $returnJson->setData(array(
          'id' => 1,
          'html' => $html,
          'result' => true,
      ));
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_CREATE_EMAIL_LAYOUT")
     */
    public function saveEmailLayoutAction(Request $request)
    {
      $entity = new EmailLayout();
      $form = $this->createNewEmailLayoutForm($entity);

      $form->handleRequest($request);

      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
       if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $responseData['message'] = 'Guardado con exito';
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_emailLayoutRow.html.twig', array(
                'emailLayout' => $entity
            ));
            $responseData['result'] = true;
            $responseData['isupdate'] = false;
            $responseData['id'] = '';
        }else{
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:emailLayoutForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }
        
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_EMAIL_LAYOUT")
     */
    public function updateEmailLayoutAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:EmailLayout')->find($id);
      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Email Layout entity.');
      }
      
      $form = $this->createNewEmailLayoutForm($entity);

      $form->handleRequest($request);

      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
       if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            $responseData['message'] = 'Guardado con exito';
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_emailLayoutRow.html.twig', array(
                'emailLayout' => $entity
            ));
            $responseData['result'] = true;
            $responseData['isupdate'] = true;
            $responseData['id'] = $id;
        }else{
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:emailLayoutForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }
        
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function editGroupAction(Request $request, $id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:UserGroup')->find($id);
      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Group entity.');
      }
      $editForm = $this->createNewNewsletterGroupForm($entity);
      
      
      $html = $this->renderView('MaithNewsletterBundle:Default:editGroup.html.twig', array(
              'form' => $editForm->createView(),
              'entity' => $entity,
            ));
      $returnJson = new JsonResponse();
      $returnJson->setData(array(
          'html' => $html,
          'result' => true,
      ));
      return $returnJson;
    }

    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function updateGroupAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MaithNewsletterBundle:UserGroup')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        $form = $this->createNewNewsletterGroupForm($entity);
        $form->handleRequest($request);

        $responseData = array(
            'result' => false,
            'message' => 'Error en el formulario',
            'html' => '',
            'id' => $id,
        );
        $html ='';
        $result = false;
        if ($form->isValid()) {
            try{
                $em->persist($entity);
                $em->flush();
                $responseData['message'] = 'Datos guardados con exito';
                $result = true;
                $html = $this->renderView('MaithNewsletterBundle:Default:_groupRow.html.twig', array(
                    'group' => $entity
                ));
            }catch(\Exception $e)
            {
                $responseData['message'] = 'El grupo ya existe.';
            }
            
        }
        if(!$result)
        {
            $html = $this->renderView('MaithNewsletterBundle:Default:editGroup.html.twig', array(
              'form' => $form->createView(),
              'entity' => $entity,
            ));
        }
        $responseData['html'] = $html;
        $responseData['result'] = $result;
        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);
        return $returnJson;
    }    
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_REMOVE_GROUP")
     */
    public function removeGroupAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();

        $entity = $em->getRepository('MaithNewsletterBundle:UserGroup')->find($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        $em->remove($entity);
        $em->flush();
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => true,
            'message' => 'Grupo eliminado correctamente',
            'html' => '',
            'id' => $id,
        ));
        return $returnJson;
    }    
    
    /**
     * 
     * @param Request $request
     * @param type $userId
     * @param type $groupId
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function removeUserOfGroupAction(Request $request, $userId, $groupId)
    {
        $em = $this->getDoctrine()->getManager();

        $group = $em->getRepository('MaithNewsletterBundle:UserGroup')->find($groupId);
        $user = $em->getRepository('MaithNewsletterBundle:User')->find($userId);
        if (!$group) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        if (!$user) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        $user->removeUserGroup($group);
        $group->removeUser($user);
        $em->persist($user);
        $em->persist($group);
        $em->flush();
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => true,
            'message' => 'Usuario del grupo eliminado correctamente',
            'html' => '',
            'userId' => $userId,
            'groupId' => $groupId,
        ));
        return $returnJson;
    }    
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function addUserOfGroupAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $group = $em->getRepository('MaithNewsletterBundle:UserGroup')->find($id);
        if (!$group) {
            throw $this->createNotFoundException('Unable to find Group entity.');
        }
        $userEmailsList = $request->get('users-selector', '');
        $explodedList = explode(",", $userEmailsList);
        $html = '';
        $message = 'Usuario(s) agregado(s) correctamente.';
        foreach($explodedList as $email)
        {
          $trimmedEmail = trim($email);
          if(!empty($trimmedEmail))
          {
            $user = $em->getRepository('MaithNewsletterBundle:User')->findOneBy(
                array('email' => $trimmedEmail)
            );
            if($user)
            {
              try{
                $user->addUserGroup($group);
                $group->addUser($user);
                $em->persist($user);
                $em->persist($group);
                $em->flush();
                $html .= $this->renderView('MaithNewsletterBundle:Default:_userGroupRow.html.twig', array(
                      'group' => $group,
                      'user' => $user,
                  ));
              } catch (\Exception $ex) {
                $message = 'Se a intentado ingresar un usuario ya existente. Operación cancelada';
              }
              
            }
          }
        }
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => true,
            'message' => $message,
            'html' => $html,
            
        ));
        return $returnJson;
    }
    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_USER")
     */
    public function userDisableAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MaithNewsletterBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $user->setActive(false);
        $em->persist($user);
        $em->flush();
        $data = array(
          'id' => $user->getId(),  
          'email' => $user->getEmail(),  
          'active' => $user->getActive(),  
        );
        $html = $this->renderView('MaithNewsletterBundle:Default:_userListRow.html.twig', array(
                      'user' => $data,
                  ));
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => true,
            'message' => 'Usuario desactivado',
            'html' => $html,
            'id' => $id,
            
        ));
        return $returnJson;
    }    
    /**
     * 
     * @param Request $request
     * @param type $id
     * @return JsonResponse
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_USER")
     */
    public function userEnableAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $em->getRepository('MaithNewsletterBundle:User')->find($id);
        if (!$user) {
            throw $this->createNotFoundException('Unable to find User entity.');
        }
        $user->setActive(true);
        $em->persist($user);
        $em->flush();
        $data = array(
          'id' => $user->getId(),  
          'email' => $user->getEmail(),  
          'active' => $user->getActive(),  
        );
        $html = $this->renderView('MaithNewsletterBundle:Default:_userListRow.html.twig', array(
                      'user' => $data,
                  ));
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => true,
            'message' => 'Usuario activado',
            'html' => $html,
            'id' => $id,
            
        ));
        return $returnJson;
    }    
}
