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

use Maith\NewsletterBundle\Form\UserType;
use Maith\NewsletterBundle\Form\UserGroupType;
use Maith\NewsletterBundle\Form\ContentType;
use Maith\NewsletterBundle\Form\ContentEditType;
use Maith\NewsletterBundle\Form\ContentSendType;

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
            'activemenu' => 'newsletter'
        ));
    }
    
    private function retrieveCreatedContents($offset = 0, $limit = 10)
    {
      $em = $this->getDoctrine()->getManager();
        
      $query = $em->createQuery("select c from MaithNewsletterBundle:Content c order by c.createdat asc")
                          ->setFirstResult($offset)
                          ->setMaxResults($limit);

      return $query->getResult();
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
    
    public function createGroupAction(Request $request)
    {
        $entity = new UserGroup();
        $form = $this->createNewNewsletterGroupForm($entity);
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
      return $this->renderView('MaithNewsletterBundle:Default:editForm.html.twig', array(
              'form' => $form->createView(),
              'formSend' => $formSend->createView(),
              'entity' => $entity,
            ));
    }
    
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
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:contentpreviewitem.html.twig', array(
                'content' => $entity
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
    
    public function previewContentAction($id)
    {
      $em = $this->getDoctrine()->getManager();

      $entity = $em->getRepository('MaithNewsletterBundle:Content')->find($id);

      if (!$entity) {
          throw $this->createNotFoundException('Unable to find Content entity.');
      }
      
      return $this->render('MaithNewsletterBundle:Default:baseNewsletter.html.twig', array(
            'entity' => $entity,
            ));
    }
    
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
    
    
    public function downloadSendedUsersAction($id)
    {
        $response = new StreamedResponse();
        $em = $this->getDoctrine()->getManager();
        $response->setCallback(function() use ($em, $id) {
     
            $handle = fopen('php://output', 'w+');
     
            // Add a row with the names of the columns for the CSV file
            fputcsv($handle, array('Email', 'Active'),';');
            // Query data from database
            
            $usersSql = "select email, active from maith_newsletter_user where id in (select maith_newsletter_user_id from maith_newsletter_content_send_user where maith_newsletter_content_send_id = ? )"; 
            $results = $em->getConnection()->executeQuery( $usersSql, array($id), array(\PDO::PARAM_INT) );
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
      $body = $this->renderView('MaithNewsletterBundle:Default:baseNewsletter.html.twig', array(
            'entity' => $content,
            ));
      $entity->setBody($body);
      
      $form = $this->createForm(new ContentSendType(), $entity, array(
          'method' => 'POST',
      ));
      
      $responseData = array(
          'result' => false,
          'message' => 'Error en el formulario',
          'html' => '',
      );
      
      $form->handleRequest($request);
      if ($form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($entity);
            $em->flush();
            
            $sendToType = $form->get('sendToType')->getData();
            $sendList = $form->get('sendlist')->getData();
          switch ($sendToType) {
            case 2:
              $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and id in (select user_id from maith_newsletter_users_groups where usergroup_id = :groupId )';
              $stmt = $em->getConnection()->prepare( $sql );
              $stmt->bindValue('contentId', $entity->getId());
              $stmt->bindValue('groupId', $sendList);
              $stmt->execute();
              break;
            case 3:
              $explodedList = explode(",", $sendList);
              $sql = 'INSERT INTO maith_newsletter_content_send_user (maith_newsletter_content_send_id, maith_newsletter_user_id , active) select :contentId, id, 1 from maith_newsletter_user where active = 1 and email = :email';
              $stmt = $em->getConnection()->prepare( $sql );
              
              foreach($explodedList as $email){
                $trimmedEmail = trim($email);
                if(!empty($trimmedEmail))
                {
                  $stmt->bindValue('contentId', $entity->getId());
                  $stmt->bindValue('email', $trimmedEmail);
                  $stmt->execute();
                }
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
    
    public function retrieveUserFormAction()
    {
      $responseData = array(
          'result' => true,
          'message' => 'Error en el formulario',
          'html' => $this->renderView('MaithNewsletterBundle:Default:usersSelector.html.twig', array(
            )),
      );
      
      $returnJson = new JsonResponse();
      $returnJson->setData($responseData);
      return $returnJson;
    }
    
    public function retrieveUsersAction(Request $request)
    {
      $term = $request->get('term').'%';
      $em = $this->getDoctrine()->getManager();
      $usersSearchSql = "select id, email, active from maith_newsletter_user where email LIKE ?"; 
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
}
