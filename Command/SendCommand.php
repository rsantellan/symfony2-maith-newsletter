<?php


namespace Maith\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Wa72\HtmlPageDom\HtmlPage;

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
        $testingMailersList = array(
            'swiftmailer.mailer.first_mailer',
            'swiftmailer.mailer.second_mailer',
            'swiftmailer.mailer.third_mailer',
            'swiftmailer.mailer.fourth_mailer',
            'swiftmailer.mailer.fifth_mailer',
        );
        $mailerList = array();
        foreach($testingMailersList as $mailer)
        {
          $found = false;
          try{
            $this->getContainer()->get($mailer);
            $found = true;
          } catch (\Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException $ex) {
            
          } catch (\Exception $ex) {

          }
          if($found)
          {
            $mailerList[] = $mailer;
          }
        }
        if(count($mailerList) == 0)
        {
          $mailerList[] = 'mailer';
        }
        $startedTime = time();
        $output->writeln('Started at : '. date('c',$startedTime));
        $container = $this->getApplication()->getKernel()->getContainer();

        $doctrine = $container->get('doctrine');
        $maximun = 500;
        $onePerNewsletterUser = true;
        $emailPerMessage = 1;
        if(false)
        {
            $emailPerMessage = 50;
        }
        $em = $doctrine->getEntityManager();
        
        $sql = "select cs.id, cs.title, cs.body from maith_newsletter_content_send cs inner join maith_newsletter_content c on c.id = cs.maith_newsletter_content_id where cs.sended = 0 and cs.sendat <= :sendDate order by c.createdat asc";
        $stmt = $em->getConnection()->prepare( $sql );
        $today = new \DateTime();
        $stmt->execute(array('sendDate' => $today->format('Y-m-d')));
        $dqlUsers = "select c from MaithNewsletterBundle:ContentSendUser c join c.user u where c.active = true and c.content = :contentSend";
        $totals = 0;
        $mailerCounter = 0;
        foreach($stmt->fetchAll() as $row)
        {
          //var_dump($row['id']);
          $sended = $em->getRepository('MaithNewsletterBundle:ContentSend')->find($row['id']);
          $users = $em->createQuery($dqlUsers)
                    ->setParameters(array(
                        'contentSend' => $sended
                    ))->setMaxResults($maximun)->getResult();
                             
          $emailsPerNewsletter = 0;
          $counter = 0;
          foreach($users as $user)
          {
            $page = new HtmlPage($row['body']);
            $links = $page->getCrawler()->filter('a');
            foreach($links as $link)
            {
              $link = new \Wa72\HtmlPageDom\HtmlPageCrawler($link);
              $string = $link->attr('href');
              if(substr_count($string, 'javascript') == 0 && substr_count($string, 'mailto') == 0)
              {
                if(substr_count($string, '?'))
                {
                  $string .= "&";
                }
                else
                {
                  $string .= "?";
                }
                $string .= "nwref=".urlencode($user->getUser()->getEmail())."&nwid=".$row['id'];
              }
              $link->attr('href', $string);
            }
            
            try
            {
              $message = \Swift_Message::newInstance()
                ->setFrom(array('hola@tekoaviajes.com.uy' => 'Tekoa Viajes'))
                ->setBody($page->save())
                ->setSubject($sended->getContent()->getTitle())
                ->setContentType("text/html");
              $message->setTo($user->getUser()->getEmail());
              $mailerName = $mailerList[$mailerCounter];
              $mailerCounter ++ ;
              if($mailerCounter >= count($mailerList))
              {
                $mailerCounter = 0;
              }
              //$this->getContainer()->get('mailer')->send($message);  
              $this->getContainer()->get($mailerName)->send($message);  
              $emailsPerNewsletter ++;  
              $totals++;
              
              if($counter == 100)
              {
                $em->flush();
                $counter = 0;
              }

            }catch(\Exception $e)
            {
              $output->writeln(sprintf('Exception [%s] -> %s', $e->getCode(), $e->getMessage()));  
            }
            $user->setActive(false);
            $em->persist($user);
            $counter ++;
            $counter++;
            if($totals == $maximun)
            {
              $sended->setQuantitySended($sended->getQuantitySended() + $emailsPerNewsletter);
              $em->persist($sended);
              $em->flush();
              $output->writeln(sprintf('Total emails sent is : %s in the time: %s', $totals, time() - $startedTime));
              return;
            }  
          }
          $sended->setActive(false);
          $sended->setSended(true);
          $sended->setQuantitySended($sended->getQuantitySended() + $emailsPerNewsletter);
          $em->persist($sended);
          
          
          
        }
        $em->flush();
        $output->writeln(sprintf('Total emails sent is : %s in the time: %s', $totals, time() - $startedTime));
        return;
    }
}


