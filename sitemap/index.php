<?php
    
    XS_Layout::getInstance()->disableFooter();
    XS_Layout::getInstance()->disableHeader();
    
    $SITEMAP = new XS_Google_SiteMap();
    
    header( 'Content-type: text/xml' );
    
    print $SITEMAP;
