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

class XS_Tag_Cloud
{
    protected $_maxWords = 0;
    protected $_words    = array();
    protected $_menu     = NULL;
    
    public function __construct( $maxWords = 25 )
    {
        $this->_maxWords = ( int )$maxWords;
        $this->_words    = XS_Database_Layer::getInstance()->getRecordsWhere( 'SEARCH_WORDS', '1', 'times DESC', $this->_maxWords );
        $this->_menu     = XS_Menu::getInstance();
    }
    
    public function __toString()
    {
        $cloud = new XS_Xhtml_Tag( 'div' );
        
        $tags    = array();
        $count   = count( $this->_words );
        $percent = 300;
        $sub     = ( $percent - 100 ) / $count;
        
        foreach( $this->_words as $word )
        {
            $percent -= $sub;
            
            $tags[] = array
            (
                'word'      => $word->word,
                'fontSize'  => round( $percent ) . '%'
            );
        }
        
        shuffle( $tags );
        
        foreach( $tags as $tag )
        {
            $span            = $cloud->span;
            $link            = $span->a;
            $link[ 'href' ]  = $this->_menu->getPageUrl( 'search' ) . '?q=' . urlencode( $tag[ 'word' ] );
            $link[ 'title' ] = $tag[ 'word' ];
            $span[ 'style' ] = 'font-size: ' . $tag[ 'fontSize' ] . ';';
            
            $link->addTextData( $tag[ 'word' ] . ' ' );
        }
        
        return ( string )$cloud;
    }
}
