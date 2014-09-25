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

class XS_Forum_Login_View extends XS_Forum_Base
{
    public function __construct()
    {
        parent::__construct();
        
        $this->_content->h3 = $this->_lang->login;
        
        if( isset( $_POST[ 'forum' ][ 'login' ] ) && $this->_user === NULL ) {
            
            $error            = $this->_content->div;
            $error[ 'class' ] = 'error';
            
            $error->addTextData( $this->_lang->loginError );
            
        } elseif( is_object( $this->_user ) ) {
            
            if( isset( $this->_params[ 'backUrl' ] ) ) {
                
                header( 'Location: ' . urldecode( $this->_params[ 'backUrl' ] ) );
                exit();
            }
            
            header( 'Location: ' . $this->_menu->getCurrentPageUrl() );
            exit();
        }
        
        $params = array( 'forum[login]' => 1 );
        
        if( isset( $this->_params[ 'backUrl' ] ) ) {
            
            $params[ 'forum[backUrl]' ] = $this->_params[ 'backUrl' ];
        }
        
        $form               = $this->_content->form;
        $form[ 'action' ]   = $this->_menu->getCurrentPageUrl( $params );
        $form[ 'name' ]     = 'forum-login';
        $form[ 'id' ]       = 'forum-login';
        $form[ 'method' ]   = 'post';
        $form[ 'class' ]    = 'forum-login';
        
        $this->_createFields( $form );
    }
    
    protected function _createFields( XS_Xhtml_tag $container )
    {
        $fields = $container->div;
        $user   = $fields->div;
        $pass   = $fields->div;
        $submit = $fields->div;
        
        $fields[ 'class' ] = 'forum-login-fields';
        $user[ 'class' ]   = 'forum-login-username';
        $pass[ 'class' ]   = 'forum-login-password';
        $submit[ 'class' ] = 'forum-login-submit';
        
        $userLabel = $user->div->label;
        $passLabel = $pass->div->label;
        
        $userLabel[ 'for' ] = 'forum-username';
        $passLabel[ 'for' ] = 'forum-password';
        
        $userLabel->addTextData( $this->_lang->username );
        $passLabel->addTextData( $this->_lang->password );
        
        $userInput   = $user->div->input;
        $passInput   = $pass->div->input;
        $submitInput = $submit->div->input;
        
        $userInput[ 'size' ] = 30;
        $passInput[ 'size' ] = 30;
        $userInput[ 'name' ] = 'forum[login][username]';
        $passInput[ 'name' ] = 'forum[login][password]';
        $userInput[ 'type' ] = 'text';
        $passInput[ 'type' ] = 'password';
        $userInput[ 'id' ]   = 'forum-username';
        $passInput[ 'id' ]   = 'forum-password';
        
        $submitInput[ 'type' ]  = 'submit';
        $submitInput[ 'value' ] = $this->_lang->submit;
    }
}
