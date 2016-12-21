<?php

namespace Maith\NewsletterBundle\Service;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use Maith\Common\AdminBundle\Services\MaithEmailService;

class NewsletterSendingService
{
    protected $em;

    protected $logger;

    protected $mailer;

    protected $strategy;

    protected $multipleEmails;

    protected $maximunPerHour;

    protected $maximunpercron;

    protected $track_links;

    protected $bodyHandler;

    public function __construct(EntityManager $em, Logger $logger, MaithEmailService $mailer, BodyHandlerInterface $bodyHandler, $strategy = 0, $maximunpercron = 15, $track_links = false)
    {
        $this->em = $em;
        $this->logger = $logger;
        $this->mailer = $mailer;
        $this->strategy = $strategy;
        $this->maximunpercron = $maximunpercron;
        $this->track_links = $track_links;
        $this->bodyHandler = $bodyHandler;
        $this->logger->addDebug('Starting Newsletter Send manager');
    }

    public function sendEmails()
    {
    	$this->logger->addDebug('Sending emails with this strategy: '.$this->strategy);
        $sql = 'select cs.id, cs.title, cs.body from maith_newsletter_content_send cs inner join maith_newsletter_content c on c.id = cs.maith_newsletter_content_id where cs.sended = 0 and cs.sendat <= :sendDate order by c.createdat asc';
        $stmt = $this->em->getConnection()->prepare($sql);
        $today = new \DateTime();
        $stmt->execute(array('sendDate' => $today->format('Y-m-d')));
        $dqlUsers = 'select c from MaithNewsletterBundle:ContentSendUser c join c.user u where c.active = true and c.content = :contentSend';

        $totals = 0;
        $mailerCounter = 0;
        foreach ($stmt->fetchAll() as $row) {
        	$sended = $this->em->getRepository('MaithNewsletterBundle:ContentSend')->find($row['id']);
            $users = $this->em->createQuery($dqlUsers)
			            ->setParameters(array(
			                'contentSend' => $sended,
			            	))
			            ->setMaxResults($this->maximunpercron)
			            ->getResult();
			foreach ($users as $user) {
                $htmlPage = $this->bodyHandler->changeBody($row['body'], $this->track_links, $user->getUser()->getEmail(), $row['id']);
                $updateUser = false;
                try{
                	$quantity = $this->mailer->send(array('rsantellan@gmail.com' => 'Rodrigo Santellan'), $user->getUser()->getEmail(), $sended->getContent()->getTitle(), $htmlPage);	
	    			$this->logger->addInfo('Sending :'.$quantity);
	    			$totals = $totals + $quantity;
	    			if($quantity > 0){
	    				$updateUser = true;
	    			}
                }catch(\Swift_RfcComplianceException $e){
                	$this->logger->error($e);
                	$updateUser = true;
                }
                
    			if($updateUser){
    				$user->setActive(false);
                	$this->em->persist($user);
    			}
            }
            if(count($users) == 0){
            	$sended->setActive(false);
            	$sended->setSended(true);
            }
            $sended->setQuantitySended($sended->getQuantitySended() + $totals);
            $this->em->persist($sended);
            $this->em->flush();
        }

        
    }

    private function sendWithLimit()
    {

    }

    private function sendAllPossible()
    {

    }

}