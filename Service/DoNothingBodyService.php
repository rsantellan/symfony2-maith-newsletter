<?php

namespace Maith\NewsletterBundle\Service;


/**
 * Description of DoNothingBodyHandler
 *
 * @author Rodrigo Santellan
 */
class DoNothingBodyService implements BodyHandlerInterface{
  
  
  public function changeBody($body, $trackLinks = false, $email = '', $id = '')
  {
    var_dump('aca');
    return $body;
  }
}
