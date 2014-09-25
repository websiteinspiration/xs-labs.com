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

final class XS_Share_Helper
{
    private static $_instance = NULL;
    protected $_menu          = NULL;
    protected $_requestUrl    = '';
    
    private function __construct()
    {
        $this->_menu       = XS_Menu::getInstance();
        $this->_requestUrl = ( ( isset( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://' )
                           . $_SERVER[ 'HTTP_HOST' ]
                           . $_SERVER[ 'REQUEST_URI' ];
    }
    
    public function __clone()
    {
        throw new XS_Singleton_Exception
        (
            'Class ' . __CLASS__ . ' cannot be cloned',
            XS_Singleton_Exception::EXCEPTION_CLONE
        );
    }
    
    public static function getInstance()
    {
        if( !is_object( self::$_instance ) )
        {
            self::$_instance = new self();
        }
        
        return self::$_instance;
    }
    
    public function twitterUrl()
    {
        $url    = new XS_Bitly_Url_Shortener( 'macmade', 'R_15147e146b0669fc6100a61adb7a1fe5' );
        $infos  = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
        $title  = 'XS-Labs';
        
        if( count( $infos ) > 3 )
        {
            array_shift( $infos );
            array_shift( $infos );
            array_pop( $infos );
            
            $title .= ' - ' . $this->_menu->getPageTitle( implode( '/', $infos ) );
        }
        
        $status = $title . ': ' . $url->shorten( $this->_requestUrl );
        
        return 'http://twitter.com/home?status=' . urlencode( $status );
    }
    
    public function facebookUrl()
    {
        return 'http://www.facebook.com/share.php?u=' . $this->_requestUrl;
    }
    
    public function stumbleUrl()
    {
        return 'http://www.stumbleupon.com/submit?url=' . $this->_requestUrl;
    }
}
