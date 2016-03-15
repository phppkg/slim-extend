<?php
/**
 *
 */
namespace slimExtend\helpers;


class TplHelper
{
    public function getPageUrl($path, $isFolder=false)
    {
        if ( $isFolder ) {
            return $path;
        }

        if( ($suffix = get_extension($path)) && ($suffix == 'md') ) {
            $pageUrl = substr($path, 0 , - (strlen($suffix)+1));

            return $pageUrl . '.html';
        }

        return 'javascript:void(0);';
    }
}