<?php


namespace Maith\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of SendCommand
 *
 * @author Rodrigo Santellan
 */
class SendCommand extends ContainerAwareCommand{
  
    protected function configure()
    {
        $this
            ->setName('newsletter:send')
            ->setDescription('Comand for sending newsletters');
        ;
    }
    
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getApplication()->getKernel()->getContainer();

        $doctrine = $container->get('doctrine');

        $onePerNewsletterUser = true;
        $emailPerMessage = 1;
        if(false)
        {
            $emailPerMessage = 50;
        }
        $em = $doctrine->getEntityManager();
        $conn = $em->getConnection();
        
        //Update query
        $sqlUpdate = 'update maith_newsletter_content_send set sended = 1, quantitySended = :quantity where id = :id';
        $stmtUpdate = $conn->prepare($sqlUpdate);
        
        $sql = 'select id, title, body from maith_newsletter_content_send where sended = 0 and sendat <= now()';
        $results = $conn->executeQuery($sql);
        $counter = 0;
        while( $row = $results->fetch())
        {
          //var_dump($dbData['id']);
          $usersSql = 'select id, email, active from maith_newsletter_user where id in (select maith_newsletter_user_id from maith_newsletter_content_send_user where maith_newsletter_content_send_id = ?) and active = 1';
          $usersResult = $em->getConnection()->executeQuery( $usersSql, array($row['id']), array(\PDO::PARAM_INT));
          $emailsData = array();
          $sendCounter = 0;
          $emailsPerNewsletter = 0;
          while( $userRow = $usersResult->fetch())
          {
              $emailsPerNewsletter ++;
              $emailsData[] = $userRow['email'];
              if($sendCounter == $emailPerMessage)
              {
                $output->writeln(sprintf('Sending -> [%s] to %s', $row['title'], implode(', ', $emailsData)));
                $message = \Swift_Message::newInstance()
                    ->setFrom(array('hola@tekoaviajes.com.uy' => 'Tekoa Viajes'))
                    ->setBody($row['body'])
                    ->setSubject($row['title'])
                    ->setContentType("text/html");
                if($emailPerMessage == 1)
                {
                    $message->setTo($emailsData);
                    $output->writeln('-');
                }
                else
                {
                    $message->setBcc($emailsData);
                }
                $counter ++;
                $this->getContainer()->get('mailer')->send($message);
                $emailsData = null;
                unset($emailsData);
                $emailsData = array();
                $sendCounter = 0;
              }
              $sendCounter++;
          }
          if(count($emailsData) > 0)
          {
              $output->writeln(sprintf('Sending -> [%s] to %s', $row['title'], implode(', ', $emailsData)));
                $message = \Swift_Message::newInstance()
                    ->setFrom(array('hola@tekoaviajes.com.uy' => 'Tekoa Viajes'))
                    ->setTo($emailsData)
                    ->setBody($row['body'])
                    ->setSubject($row['title'])
                    ->setContentType("text/html");
                    $counter ++;
                $emailsPerNewsletter ++;
                $this->getContainer()->get('mailer')->send($message);
          }
          $stmtUpdate->bindValue('id', $row['id']);
          $stmtUpdate->bindValue('quantity', $emailsPerNewsletter);
          $stmtUpdate->execute();
        }
        
        $output->writeln(sprintf('Sended %s emails', $counter));
    }
}


