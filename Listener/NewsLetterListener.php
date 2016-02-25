<?php

namespace Maith\NewsletterBundle\Listener;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

/**
 * Description of NewsLetterListener
 *
 * @author Rodrigo Santellan
 */
class NewsLetterListener {
  
  private $em;
  private $logger;
  
  function __construct(EntityManager $em, Logger $logger)
  {
      $this->em = $em;
      $this->logger = $logger;
  }
  
  public function onKernelRequest(GetResponseEvent $event) {
    
    if (HttpKernelInterface::MASTER_REQUEST !== $event->getRequestType()) {
      return;
    }
    $request = $event->getRequest();
    if ($request->get('nwref') && $request->get('nwid')) {
      $sended = $this->em->getRepository('MaithNewsletterBundle:ContentSend')->find($request->get('nwid'));
      if($sended)
      {
        $this->logger->info(sprintf('User %s has a hit on newsletter sended: %s', $request->get('nwref'), $request->get('nwid')));
        //$user = $this->em->getRepository('MaithNewsletterBundle:User')->findOneBy(array('email' => $request->get('nwref')));
        $dql = "select c from MaithNewsletterBundle:ContentSendUser c join c.user u where u.email = :email and c.content = :contentSend";
        $user = $this->em->createQuery($dql)
                    ->setParameters(array(
                        'contentSend' => $sended,
                        'email' => $request->get('nwref'),
                    ))->setMaxResults(1)->getOneOrNullResult();
        if($user)
        {
          $user->setHits($user->getHits() + 1);
          $this->em->persist($user);
          $this->em->flush();
        }
      }
      else
      {
        $this->logger->addError(sprintf("The sended newsletter with id: %s don't exists", $request->get('nwid')));
      }
      
    }
  }
}
