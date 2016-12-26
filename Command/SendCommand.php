<?php

namespace Maith\NewsletterBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Description of SendCommand.
 *
 * @author Rodrigo Santellan
 */
class SendCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('newsletter:send')
            ->setDescription('Comand for sending newsletters');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $startedTime = time();
        $output->writeln('Started at : '.date('c', $startedTime));
        $obj = $this->getContainer()->get('maith_newsletter_sender');
        $obj->sendEmails();
        $output->writeln(sprintf('Emails sent in the time: %s', time() - $startedTime));
        return;
    }
}
