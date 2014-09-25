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

class XS_Forum_Profile_View extends XS_Forum_Base
{
    protected $_errors = NULL;
    protected $_data   = array();
    
    public function __construct()
    {
        parent::__construct();
        
        $this->_errors = new stdClass();
        $valid         = false;
        
        if( isset( $_POST[ 'forum' ][ 'profileEdit' ] ) ) {
            
            $this->_data = $_POST[ 'forum' ][ 'profileEdit' ];
            $valid       = $this->_checkFormValues();
        }
        
        if( !is_object( $this->_user ) ) {
            
            header( 'Location: ' . $this->_menu->getCurrentPageUrl() );
            exit();
        }
        
        if( $valid === true ) {
            
            $this->_updateUser();
            
            $confirm            = $this->_content->div;
            $confirm[ 'class' ] = 'forum-profile-confirm';
            $confirm->h3        = $this->_lang->titleProfileUpdateSuccess;
            $confirm->div      = $this->_lang->titleProfileUpdateSuccessText;
            
        }
        
        $this->_content->h3 = $this->_lang->titleProfile;
        $form               = $this->_content->form;
        $form[ 'action' ]   = $this->_menu->getCurrentPageUrl( array( 'forum[profile]' => 1 ) );
        $form[ 'name' ]     = 'forum-profile';
        $form[ 'id' ]       = 'forum-profile';
        $form[ 'method' ]   = 'post';
        $form[ 'class' ]    = 'forum-profile';
        
        $this->_createFields( $form );
    }
    
    protected function _updateUser()
    {
        $this->_user->firstname = $this->_data[ 'firstname' ];
        $this->_user->lastname  = $this->_data[ 'lastname' ];
        $this->_user->username  = $this->_data[ 'username' ];
        $this->_user->email     = $this->_data[ 'email' ];
        $this->_user->company   = ( isset( $this->_data[ 'company' ] ) ) ? $this->_data[ 'company' ] : '';
        $this->_user->www       = ( isset( $this->_data[ 'www' ] ) )     ? $this->_data[ 'www' ]     : '';
        
        if( $this->_data[ 'password' ] ) {
            
            $this->_user->password  = sha1( $this->_data[ 'password' ] );
        }
        
        $this->_user->commit();
        
        $this->_userOptions->show_fullname = ( int )( isset( $this->_data[ 'show_fullname' ] ) && $this->_data[ 'show_fullname' ] );
        
        $this->_userOptions->commit();
    }
    
    protected function _checkFormValues()
    {
        $this->_checkRequired( 'firstname' );
        $this->_checkRequired( 'lastname' );
        $this->_checkRequired( 'username' );
        $this->_checkRequired( 'email' );
        $this->_checkEmail(    'email' );
        
        if( $this->_checkUniqueInDatabase( 'username', self::TABLE_USERS, 'username' ) === false ) {
            
            $this->_errors->username = $this->_lang->errorUsernameNotUnique;
        }
        
        if( $this->_checkUniqueInDatabase( 'email', self::TABLE_USERS, 'email' ) === false ) {
            
            $this->_errors->email = $this->_lang->errorEmailNotUnique;
        }
        
        $valid = true;
        
        foreach( $this->_errors as $field => $errorText ) {
            
            $error            = new XS_Xhtml_Tag( 'div' );
            $error[ 'class' ] = 'forum-field-error';
            
            $error->addTextData( $errorText );
            
            $this->_errors->$field = $error;
            $valid                 = false;
        }
        
        return $valid;
    }
    
    protected function _checkRequired( $name )
    {
        if( !isset( $this->_data[ $name ] ) || !$this->_data[ $name ] ) {
            
            $this->_errors->$name = $this->_lang->errorRequired;
        }
    }
    
    protected function _checkEmail( $name )
    {
        if( !isset( $this->_data[ $name ] ) ) {
            
            return;
        }
        
        if( !filter_var( $this->_data[ $name ], FILTER_VALIDATE_EMAIL ) ) {
            
            $this->_errors->$name = $this->_lang->errorInvalidEmail;
        }
    }
    
    protected function _checkUniqueInDatabase( $name, $table, $column )
    {
        if( !isset( $this->_data[ $name ] ) ) {
            
            return false;
        }
        
        if( $this->_data[ $name ] == $this->_user->$name ) {
            
            return true;
        }
        
        $records = $this->_db->getRecordsByFields(
            $table,
            array(
                'deleted' => 0,
                $name     => $this->_data[ $name ]
            )
        );
        
        return ( count( $records ) === 0 );
    }
    
    protected function _createFields( XS_Xhtml_Tag $form )
    {
        $this->_createTextInput(    'firstname',    $form );
        $this->_createTextInput(    'lastname',     $form );
        $this->_createTextInput(    'username',     $form );
        $this->_createTextPassword( 'password',     $form );
        $this->_createTextInput(    'email',        $form );
        $this->_createTextInput(    'company',      $form );
        $this->_createTextInput(    'www',          $form );
        $this->_createCheckbox(     'show_fullname', $form );
        
        $this->_createSubmit( $form );
    }
    
    protected function _createTextInput( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $inputDiv = $field->div;
        $label    = $labelDiv->label;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $labelDiv[ 'class' ] = 'forum-field-label';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $input[ 'id' ]    = 'forum-field-' . $name;
        $input[ 'type' ]  = 'text';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = ( isset( $this->_data[ $name ] ) ) ? $this->_data[ $name ] : $this->_user->$name;
        $input[ 'name' ]  = 'forum[profileEdit][' . $name . ']';
        
        if( isset( $this->_errors->$name ) ) {
            
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createTextPassword( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $labelDiv = $field->div;
        $inputDiv = $field->div;
        $label    = $labelDiv->label;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $labelDiv[ 'class' ] = 'forum-field-label';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $input[ 'id' ]    = 'forum-field-' . $name;
        $input[ 'type' ]  = 'password';
        $input[ 'size' ]  = '50';
        $input[ 'value' ] = '';
        $input[ 'name' ]  = 'forum[profileEdit][' . $name . ']';
        
        if( isset( $this->_errors->$name ) ) {
            
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createCheckbox( $name, XS_Xhtml_Tag $container )
    {
        $div      = $container->div;
        $field    = $div->div;
        $inputDiv = $field->div;
        $input    = $inputDiv->input;
        $label    = $inputDiv->label;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        
        $label->addTextData( $this->_lang->$name );
        
        $label[ 'for' ]   = 'forum-field-' . $name;
        $input[ 'id' ]    = 'forum-field-' . $name;
        $input[ 'type' ]  = 'checkbox';
        $input[ 'value' ] = '1';
        $input[ 'name' ]  = 'forum[profileEdit][' . $name . ']';
        
        if( isset( $this->_data[ $name ] ) && $this->_data[ $name ] ) {
            
            $input[ 'checked' ] = 'checked';
            
        } elseif( $this->_userOptions->$name ) {
            
            $input[ 'checked' ] = 'checked';
        }
        
        if( isset( $this->_errors->$name ) ) {
            
            $div->addChildNode( $this->_errors->$name );
        }
    }
    
    protected function _createSubmit( XS_Xhtml_Tag $container, $name = 'submit' )
    {
        $div      = $container->div;
        $field    = $div->div;
        $inputDiv = $field->div;
        $input    = $inputDiv->input;
        
        $div[ 'class' ]      = 'forum-field-' . $name;
        $field[ 'class' ]    = 'forum-field';
        $inputDiv[ 'class' ] = 'forum-field-formElement';
        $input[ 'id' ]       = 'forum-field-' . $name;
        $input[ 'type' ]     = 'submit';
        $input[ 'value' ]    = $this->_lang->$name;
        $input[ 'name' ]     = 'forum[profileEdit][' . $name . ']';
    }
}
