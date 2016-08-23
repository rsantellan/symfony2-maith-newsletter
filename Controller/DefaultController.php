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
 * @PreAuthorize("hasRole('ROLE_MANAGE_NEWSLETTER')")
 */
class DefaultController extends Controller
{
    private $limitContent = 10;

    public function indexAction()
    {
        $newsletterService = $this->get('maith_newsletter');
        $quantity = $newsletterService->retrieveNewsletterUsersQuantity();
        $groups = $newsletterService->retrieveAllNewsletterGroups();
        $emailLayouts = $newsletterService->retrieveAllEmailsLayouts();

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
            'contents' => $newsletterService->retrieveCreatedContents(0, $this->limitContent),
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
          'contents' => $newsletterService->retrieveCreatedContents($offset, $this->limitContent),
          'pager' => $page,
          'limitContent' => $this->limitContent,
        ));

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
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
     * @param Request $request
     *
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
            $newsletterService = $this->get('maith_newsletter');
            $responseData['message'] = $newsletterService->saveNewsletterUser($entity);
            $createUserForm = $this->createNewNewsletterUserForm(new User());
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:userForm.html.twig', array(
              'userform' => $createUserForm->createView(),
            ));

            $responseData['result'] = true;
        } else {
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:userForm.html.twig', array(
              'userform' => $form->createView(),
            ));
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @param Request $request
     *
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
            $newsletterService = $this->get('maith_newsletter');

            $group = $newsletterService->saveNewsletterGroup($entity);
            if ($group) {
                $responseData['message'] = 'Datos guardados con exito';
                $responseData['listhtml'] = $this->renderView('MaithNewsletterBundle:Default:_groupRow.html.twig', array(
                    'group' => $group,
                ));
            } else {
                $responseData['message'] = 'El grupo ya existe.';
            }
            $createGroupForm = $this->createNewNewsletterGroupForm(new UserGroup());
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:groupForm.html.twig', array(
              'groupform' => $createGroupForm->createView(),
            ));

            $responseData['result'] = true;
        } else {
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:groupForm.html.twig', array(
              'groupform' => $form->createView(),
            ));
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
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
     * @param type $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function editContentAction($id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveNewsletterContent($id);

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

    private function createEditContent($entity, $form)
    {
        $formSend = $this->createForm(new ContentSendType(), new ContentSend(), array(
          'method' => 'POST',
      ));

        $newsletterService = $this->get('maith_newsletter');
        $emailLayouts = $newsletterService->retrieveAllEmailsLayouts(true);

        return $this->renderView('MaithNewsletterBundle:Default:editForm.html.twig', array(
              'form' => $form->createView(),
              'formSend' => $formSend->createView(),
              'entity' => $entity,
              'emailLayouts' => $emailLayouts,
            ));
    }

    /**
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function updateContentAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveNewsletterContent($id);

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
            $entity = $newsletterService->persistContent($entity);
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->createEditContent($entity, $form);
            $responseData['result'] = true;
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @param Request $request
     *
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
            $newsletterService = $this->get('maith_newsletter');
            $entity = $newsletterService->persistContent($entity);
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->createEditContent($entity, $form);
            $aux = array(
              'title' => $entity->getTitle(),
              'id' => $entity->getId(),
              'created' => 0,
              'sended' => 0,
            );
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_contentTableRow.html.twig', array(
                'content' => $aux,
            ));
            $responseData['result'] = true;
        } else {
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:composeForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @return StreamedResponse
     * @Secure(roles="ROLE_NEWSLETTER_DOWNLOAD_USERS")
     */
    public function downloadUsersAction()
    {
        $response = new StreamedResponse();
        $newsletterService = $this->get('maith_newsletter');
        $response->setCallback(function () use ($newsletterService) {

            $handle = fopen('php://output', 'w+');
            // Add a row with the names of the columns for the CSV file
            fputcsv($handle, array('Email', 'Active'), ';');
            // Query data from database
            $results = $newsletterService->retrieveUserSqlCursor();
            // Add the data queried from database
            while ($row = $results->fetch()) {
                fputcsv($handle, array($row['email'],
                    $row['active'],
                ), ';');
            }
            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }

    /**
     * @param Request $request
     *
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
             $newsletterService = $this->get('maith_newsletter');
            $newsletterService->saveNewsletterUserCsvFile($file->getData()->getPathname());
        }
        $this->get('session')->getFlashBag()->add('notif-success', sprintf('Se agregaron %s contactos', $counter));

        return $this->redirect($this->generateUrl('admin_newsletter_index'));
    }

    /**
     * @param Request $request
     * @param type    $id
     *
     * @return type
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function previewContentAction(Request $request, $id)
    {
        $layout = $request->get('layout', null);
        $entityLayout = null;

        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveNewsletterContent($id);
        if ($layout) {
            $entityLayout = $newsletterService->retrieveNewsletterLayout($layout);
        }
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Content entity.');
        }
        if ($layout && !$entityLayout) {
            throw $this->createNotFoundException('Unable to find Email layout entity.');
        }
        $newBody = '';
        if ($layout) {
            $newsletterService = $this->get('maith_newsletter');
            $dbBody = $newsletterService->retrieveContentLayoutBody($id);
            $newBody = str_replace('[[body]]', $entity->getBody(), $dbBody);
        } else {
            $newBody = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterBody.html.twig', array(
            'entity' => $entity,
            ));
        }

        return $this->render('MaithNewsletterBundle:Default:baseNewsletterHeader.html.twig', array(
          'bodycontent' => $newBody,
          ));
    }

    /**
     * @param type $id
     *
     * @return type
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function previewSendContentAction($id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveNewsletterContentSend($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Content Sended entity.');
        }

        return $this->render('MaithNewsletterBundle:Default:contentSend.html.twig', array(
            'entity' => $entity,
            'body' => stream_get_contents($entity->getBody()),
            ));
    }

    /**
     * @param type $id
     *
     * @return StreamedResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function downloadSendedUsersAction($id)
    {
        $response = new StreamedResponse();
        $newsletterService = $this->get('maith_newsletter');
        $response->setCallback(function () use ($newsletterService, $id) {
            $handle = fopen('php://output', 'w+');
            // Add a row with the names of the columns for the CSV file
            fputcsv($handle, array('Email', 'Hits'), ';');
            // Query data from database
            $results = $newsletterService->retrieveSendedUserSqlCursor($id);
            // Add the data queried from database
            while ($row = $results->fetch()) {
                fputcsv($handle, array($row['email'],
                    $row['hits'],
                ), ';');
            }

            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export.csv"');

        return $response;
    }

    /**
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function sendContentQueueAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $content = $newsletterService->retrieveNewsletterContent($id);
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
        if ($emailLayout) {
            $emailLayoutBody = $newsletterService->retrieveContentLayoutBody($emailLayout->getId());
            $newBody = str_replace('[[body]]', $content->getBody(), $emailLayoutBody);
            $fullBody = $this->renderView('MaithNewsletterBundle:Default:baseNewsletterHeader.html.twig', array(
            'bodycontent' => $newBody,
            ));
            $entity->setBody($fullBody);
        }

        if ($form->isValid()) {
            $entity = $newsletterService->persistContentSend($entity);

            $sendToType = $form->get('sendToType')->getData();
            $sendList = $form->get('sendlist')->getData();
            $sendlistIds = $form->get('sendlistIds')->getData();
            switch ($sendToType) {
            case 2:
                $explodedList = explode(',', $sendList);
                $newsletterService->sendContentToGroups($entity->getId(), $explodedList);
              break;
            case 3:
                  $explodedList = explode(',', $sendList);
                  $explodedListIds = explode(',', $sendlistIds);
                  $newsletterService->sendContentToUsers($entity->getId(), $explodedList, $explodedListIds);
              break;
            default:
                  $newsletterService->sendContentToAll($entity->getId());
              break;
          }
            $responseData['message'] = 'Guardado con exito';
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:sendedRow.html.twig', array(
              'sended' => $entity,
            ));
            $responseData['result'] = true;
        } else {
            //$responseData['html'] = $this->createEditContent($entity, $form);
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function retrieveGroupsAction()
    {
        $newsletterService = $this->get('maith_newsletter');
        $groups = $newsletterService->retrieveAllNewsletterGroups();
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
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function retrieveUserFormAction()
    {
        $newsletterService = $this->get('maith_newsletter');
        $showGroups = true;
        $groupUsersLimit = 50;
        $groupData = $newsletterService->retrieveUsersToSendList($groupUsersLimit);
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
              $rowClass = 'col-lg-6';
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
     * @param Request $request
     *
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function retrieveUsersAction(Request $request)
    {
        $newsletterService = $this->get('maith_newsletter');
        $returnData = $newsletterService->retrieveUserForSearch($request->get('term'));
        $returnJson = new JsonResponse();
        $returnJson->setData($returnData);

        return $returnJson;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_SEARCH_USER")
     */
    public function searchListUsersAction(Request $request)
    {
        $newsletterService = $this->get('maith_newsletter');
        $list = $newsletterService->retrieveUserSearchWithLimit($request->get('search'));
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
     * @param type $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_CONTENT")
     */
    public function removeSendedContentAction($id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $message = 'Envio eliminado correctamente';
        $result = true;
        try {
            $newsletterService->removeContentSend($id);
        } catch (\Exception $e) {
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

    /**
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_EMAIL_LAYOUT")
     */
    public function editEmailLayoutAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');

        $entity = $newsletterService->retrieveNewsletterLayout($id);
        if (!$entity) {
            throw $this->createNotFoundException('Unable to find Email Layout entity.');
        }
        $default = new EmailLayout();
        $body = $newsletterService->retrieveContentLayoutBody($id);
        $default->setBody($body);
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
     * @param Request $request
     *
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
            $newsletterService = $this->get('maith_newsletter');
            $entity = $newsletterService->persistEmailLayout($entity);
            $responseData['message'] = 'Guardado con exito';
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_emailLayoutRow.html.twig', array(
                'emailLayout' => $entity,
            ));
            $responseData['result'] = true;
            $responseData['isupdate'] = false;
            $responseData['id'] = '';
        } else {
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:emailLayoutForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @param Request $request
     *
     * @return JsonResponse
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_EMAIL_LAYOUT")
     */
    public function updateEmailLayoutAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveNewsletterLayout($id);

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
            $entity = $newsletterService->persistEmailLayout($entity);
            $responseData['message'] = 'Guardado con exito';
            $responseData['listHtml'] = $this->renderView('MaithNewsletterBundle:Default:_emailLayoutRow.html.twig', array(
                'emailLayout' => $entity,
            ));
            $responseData['result'] = true;
            $responseData['isupdate'] = true;
            $responseData['id'] = $id;
        } else {
            $responseData['html'] = $this->renderView('MaithNewsletterBundle:Default:emailLayoutForm.html.twig', array(
              'form' => $form->createView(),
            ));
        }

        $returnJson = new JsonResponse();
        $returnJson->setData($responseData);

        return $returnJson;
    }

    /**
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function editGroupAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveUserGroup($id);
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
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function updateGroupAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $entity = $newsletterService->retrieveUserGroup($id);

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
        $html = '';
        $result = false;
        if ($form->isValid()) {
            try {
                $entity = $newsletterService->persistUserGroup($entity);
                $responseData['message'] = 'Datos guardados con exito';
                $result = true;
                $html = $this->renderView('MaithNewsletterBundle:Default:_groupRow.html.twig', array(
                    'group' => $entity,
                ));
            } catch (\Exception $e) {
                $responseData['message'] = 'El grupo ya existe.';
            }
        }
        if (!$result) {
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
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_REMOVE_GROUP")
     */
    public function removeGroupAction(Request $request, $id)
    {
        $newsletterService = $this->get('maith_newsletter');
        $result = true;
        try {
            $newsletterService->removeUserGroup($id);
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result = false;
        }
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
     * @param Request $request
     * @param type    $userId
     * @param type    $groupId
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function removeUserOfGroupAction(Request $request, $userId, $groupId)
    {
        $newsletterService = $this->get('maith_newsletter');
        try {
            $newsletterService->removeUserOfGroup($userid, $groupId);
        } catch (\Exception $e) {
        }
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
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_GROUP")
     */
    public function addUserOfGroupAction(Request $request, $id)
    {
        $userEmailsList = $request->get('users-selector', '');
        $explodedList = explode(',', $userEmailsList);
        $html = '';
        $message = 'Usuario(s) agregado(s) correctamente.';
        $newsletterService = $this->get('maith_newsletter');
        $usersLists = $newsletterService->addUserToGroup($id, $explodedList);
        foreach ($usersLists as $user) {
            $html .= $this->renderView('MaithNewsletterBundle:Default:_userGroupRow.html.twig', array(
                  'group' => $group,
                  'user' => $user,
              ));
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
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_USER")
     */
    public function userDisableAction(Request $request, $id)
    {
        $message = 'Usuario desactivado';
        $result = true;
        $html = '';
        try {
            $newsletterService = $this->get('maith_newsletter');
            $user = $newsletterService->changeActiveUserStatue($id, false);
            $data = array(
              'id' => $user->getId(),
              'email' => $user->getEmail(),
              'active' => $user->getActive(),
            );
            $html = $this->renderView('MaithNewsletterBundle:Default:_userListRow.html.twig', array(
                          'user' => $data,
                      ));
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result = false;
        }
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => $result,
            'message' => $message,
            'html' => $html,
            'id' => $id,

        ));

        return $returnJson;
    }
    /**
     * @param Request $request
     * @param type    $id
     *
     * @return JsonResponse
     *
     * @throws type
     * @Secure(roles="ROLE_NEWSLETTER_EDIT_USER")
     */
    public function userEnableAction(Request $request, $id)
    {
        $message = 'Usuario activado';
        $result = true;
        $html = '';
        try {
            $newsletterService = $this->get('maith_newsletter');
            $user = $newsletterService->changeActiveUserStatue($id, true);
            $data = array(
              'id' => $user->getId(),
              'email' => $user->getEmail(),
              'active' => $user->getActive(),
            );
            $html = $this->renderView('MaithNewsletterBundle:Default:_userListRow.html.twig', array(
                          'user' => $data,
                      ));
        } catch (\Exception $e) {
            $message = $e->getMessage();
            $result = false;
        }
        $returnJson = new JsonResponse();
        $returnJson->setData(array(
            'result' => $result,
            'message' => $message,
            'html' => $html,
            'id' => $id,

        ));

        return $returnJson;
    }
}
