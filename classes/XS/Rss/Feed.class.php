<?php

################################################################################
# Copyright (c) 2010, Jean-David Gadina <macmade@xs-labs.com>                  #
# All rights reserved.                                                         #
#                                                                              #
# Redistribution and use in source and binary forms, with or without           #
# modification, are permitted provided that the following conditions are met:  #
#                                                                              #
#  -   Redistributions of source code must retain the above copyright notice,  #
#      this list of conditions and the following disclaimer.                   #
#  -   Redistributions in binary form must reproduce the above copyright       #
#      notice, this list of conditions and the following disclaimer in the     #
#      documentation and/or other materials provided with the distribution.    #
#  -   Neither the name of 'Jean-David Gadina' nor the names of its            #
#      contributors may be used to endorse or promote products derived from    #
#      this software without specific prior written permission.                #
#                                                                              #
# THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS "AS IS"  #
# AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT LIMITED TO, THE    #
# IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR A PARTICULAR PURPOSE   #
# ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT HOLDER OR CONTRIBUTORS BE    #
# LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL, SPECIAL, EXEMPLARY, OR          #
# CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT LIMITED TO, PROCUREMENT OF         #
# SUBSTITUTE GOODS OR SERVICES; LOSS OF USE, DATA, OR PROFITS; OR BUSINESS     #
# INTERRUPTION) HOWEVER CAUSED AND ON ANY THEORY OF LIABILITY, WHETHER IN      #
# CONTRACT, STRICT LIABILITY, OR TORT (INCLUDING NEGLIGENCE OR OTHERWISE)      #
# ARISING IN ANY WAY OUT OF THE USE OF THIS SOFTWARE, EVEN IF ADVISED OF THE   #
# POSSIBILITY OF SUCH DAMAGE.                                                  #
################################################################################

# $Id$

abstract class XS_Rss_Feed
{
    const RSS_VERSION   = '2.0';
    const RSS_GENERATOR = 'XS-Labs WCMS';
    const RSS_DOCS      = 'http://blogs.law.harvard.edu/tech/rss';
    
    protected $_menu      = NULL;
    protected $_lang      = '';
    protected $_host      = '';
    protected $_rss       = NULL;
    
    public function __construct()
    {
        $this->_menu            = XS_Menu::getInstance();
        $this->_lang            = $this->_menu->getLanguage();
        $this->_rss             = simplexml_load_string( '<rss version="' . self::RSS_VERSION . '"></rss>' );
        $channel                = $this->_rss->addChild( 'channel' );
        $channel->language      = $this->_lang;
        $channel->generator     = self::RSS_GENERATOR;
        $channel->docs          = self::RSS_DOCS;
        $channel->lastBuildDate = date( 'r', time() );
        $this->_host            = ( isset( $_SERVER[ 'SSL' ] ) ) ? 'https://' . $_SERVER[ 'HTTP_HOST' ] : 'http://' . $_SERVER[ 'HTTP_HOST' ];
    }
    
    public function __toString()
    {
        return $this->_rss->asXML();
    }
    
    public function setTitle( $value )
    {
        $this->_rss->channel->title = ( string )$value;
    }
    
    public function setLink( $value )
    {
        $this->_rss->channel->link = $this->_host . ( string )$value;
    }
    
    public function setDescription( $value )
    {
        $this->_rss->channel->description = ( string )$value;
    }
}
