<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace Maith\NewsletterBundle\Service;

/**
 * Description of TrackLinksBodyService.
 *
 * @author rodrigo
 */
class TrackLinksBodyService implements BodyHandlerInterface
{
    public function changeBody($body, $trackLinks = false, $email = '', $id = '')
    {
        if ($trackLinks) {
            $page = new \Wa72\HtmlPageDom\HtmlPage($body);
            $links = $page->getCrawler()->filter('a');
            foreach ($links as $link) {
                $link = new \Wa72\HtmlPageDom\HtmlPageCrawler($link);
                $string = $link->attr('href');
                if (substr_count($string, 'javascript') == 0 && substr_count($string, 'mailto') == 0) {
                    if (substr_count($string, '?')) {
                        $string .= '&';
                    } else {
                        $string .= '?';
                    }
                    $string .= 'nwref='.urlencode($email).'&nwid='.$id;
                }
                $link->attr('href', $string);
            }
            $htmlPage = $page->save();
        } else {
            $htmlPage = $body;
        }
    }
}
