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

class XS_Forum extends XS_Forum_Base
{
    public function __construct()
    {
        if( isset( $_POST[ 'forum' ][ 'login' ] ) ) {
            
            $this->_login();
        }
        
        parent::__construct();
        
        $view              = NULL;
        $status            = $this->_content->div;
        $status[ 'class' ] = 'forum-connection-status';
        
        if( isset( $this->_params[ 'logout' ] ) ) {
            
            unset( $this->_session->user_id );
            
            $this->_user   = NULL;
            $this->_params = array();
        }
        
        if( $this->_user === NULL ) {
            
            $url = $this->_menu->getCurrentPageUrl();
            
            if( count( $this->_params ) ) {
                
                $url .= '?';
                
                foreach( $this->_params as $key => $value ) {
                    
                    $url .= 'forum[' . $key . ']=' . $value . '&';
                }
                
                $url = substr( $url, 0, -1 );
            }
            
            $loginLink           = new XS_Xhtml_Tag( 'a' );
            $loginLink[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[login]'   => 1,
                    'forum[backUrl]' => $url
                )
            );
            
            $loginLink->addTextData( $this->_lang->login );
            
            $registerLink           = new XS_Xhtml_Tag( 'a' );
            $registerLink[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[register]' => 1
                )
            );
            
            $registerLink->addTextData( $this->_lang->register );
            
            $status->span = sprintf(
                $this->_lang->connectionStatusNotConnected,
                $loginLink,
                $registerLink
            );
            
        } else {
            
            $logoutLink           = new XS_Xhtml_Tag( 'a' );
            $logoutLink[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[logout]' => 1
                )
            );
            
            $logoutLink->addTextData( $this->_lang->logout );
            
            $profileLink           = new XS_Xhtml_Tag( 'a' );
            $profileLink[ 'href' ] = $this->_menu->getCurrentPageUrl(
                array(
                    'forum[profile]' => 1
                )
            );
            
            $profileLink->addTextData( $this->_user->username );
            
            $status->span = sprintf(
                $this->_lang->connectionStatusConnected,
                ( string )$profileLink,
                $logoutLink
            );
        }
        
        $searchBox = new XS_Forum_Search_Box();
        
        try {
            
            if( isset( $this->_params[ 'login' ] ) ) {
                
                $view = new XS_Forum_Login_View();
                
            } elseif( isset( $this->_params[ 'register' ] ) ) {
                
                $view = new XS_Forum_Register_View();
                
            } elseif( isset( $this->_params[ 'profile' ] ) ) {
                
                $view = new XS_Forum_Profile_View();
                
            } elseif( isset( $this->_params[ 'newThread' ] ) ) {
                
                $view = new XS_Forum_Thread_Create_View( ( int )$this->_params[ 'newThread' ] );
                
            } elseif( isset( $this->_params[ 'thread' ] ) ) {
                
                $this->_content->addChildNode( $searchBox->getContent() );
                
                $view = new XS_Forum_Thread_View( ( int )$this->_params[ 'thread' ] );
                
            } elseif( isset( $this->_params[ 'search' ] ) ) {
                
                $this->_content->addChildNode( $searchBox->getContent() );
                
                $view = new XS_Forum_Search_View();
                
            } elseif( isset( $this->_params[ 'category' ] ) ) {
                
                $this->_content->addChildNode( $searchBox->getContent() );
                
                $view = new XS_Forum_Category_View( ( int )$this->_params[ 'category' ] );
                
            } else {
                
                $this->_content->addChildNode( $searchBox->getContent() );
                
                $view = new XS_Forum_View();
            }
            
            $this->_content->addChildNode( $view->getContent() );
            
        } catch( XS_Exception_Base $e ) {
            
            $this->_error( $e );
        }
    }
    
    protected function _error( XS_Exception_Base $e )
    {
        $error            = $this->_content->div;
        $error[ 'class' ] = 'exception';
        
        $error->h6 = sprintf( $this->_lang->error, $e->getCode() );
        $error->div = $e->getMessage();
    }
    
    protected function _login()
    {
        $data = $_POST[ 'forum' ];
        
        if( !isset( $data[ 'login' ][ 'username' ] ) || !isset( $data[ 'login' ][ 'password' ] ) ) {
            
            return;
        }
        
        $users = XS_Database_Object::getObjectsByFields(
            self::TABLE_USERS,
            array(
                'deleted'  => 0,
                'username' => $data[ 'login' ][ 'username' ],
                'password' => sha1( $data[ 'login' ][ 'password' ] )
            )
        );
        
        if( count( $users ) ) {
            
            $session               = XS_Session::getInstance();
            $user                  = array_shift( $users );
            $session->user_id      = $user->getId();
            $user->session         = $session->getId();
            $user->lastlogin      = time();
            
            if( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
                
                $user->lastip = $_SERVER[ 'REMOTE_ADDR' ];
            }
            
            $user->commit();
        }
    }
}
