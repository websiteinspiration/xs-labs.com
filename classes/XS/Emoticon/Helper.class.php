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

final class XS_Emoticon_Helper
{
    CONST EMOTICONS_DIR = '/css/pictures/emoticons/';
    
    private static $_instance = NULL;
    private $_emoticons       = array
    (
        'devil.png'     => array( '&gt;:)' ),
        'ninja.png'     => array( '(:)', '(ph33r)', '(ph34r)' ),
        'joyful.png'    => array( '^_^', '^-^', '^^', ':))', ':-))' ),
        'alien.png'     => array( '=:)', '(alien)' ),
        'bandit.png'    => array( '(bandit)', ':(&gt;' ),
        'annoyed.png'   => array( '&gt;:o', '&gt;:-o', '&gt;:O', '&gt;:-O', 'X(', 'X-(', 'x(', 'x-(', ':@', '&lt;_&lt;' ),
        'crying.png'    => array( ':\'(', '=\'(' ),
        'pouty.png'     => array( ':|', '=|', ':-|' ),
        'surprised.png' => array( ':-O', ':O', ':-o', ':o', ':-0', '=-O', '=-o', '=o', '=O' ),
        'biggrin.png'   => array( ':-d', ':d', ':-D', ':D', ':d', '=D', '=-D' ),
        'innocent.png'  => array( 'O:)', 'o:)', 'o:-)', 'O:-)', '0:)', '0:-)' ),
        'kissing.png'   => array( ':-*', ':*' ),
        'smile.png'     => array( ':-)', ':)' ),
        'tongue.png'    => array( ':P', '=P', '=p', ':-P', ':p', ':-p', ':b' ),
        'cool.png'      => array( 'B)', 'B-)', '8)' ),
        'wink.png'      => array( ';)', ';-)', ';&gt;' ),
        'happy.png'     => array( '=)', '=-)', '=))', ':))' ),
        'andy.png'      => array( 'o_O', 'o_0', 'O_O', 'o_o', 'O_o', '0_o', 'o0', '0o', 'oO', 'Oo', '0_0' ),
        'unsure.png'    => array( ':\\', ':/', '=/', '=\\', ':-/', ':-\\', ':s', ':-S', ':-s', ':S' ),
        'lol.png'       => array( '(LOL)' ),
        'pinched.png'   => array( '&gt;_&lt;' ),
        'rightful.png'  => array( '(police)', '(cop)', '{):)' ),
        'sad.png'       => array( ':(', '=(', '=-(', ':-(' ),
        'sick.png'      => array( ':&', ':-&' ),
        'sideways.png'  => array( '=]' ),
        'sleeping.png'  => array( '-_-', '-.-', '(-.-)', '|)', '|-)', 'I-)', 'I-|' ),
        'w00t.png'      => array( '(woot)', '(w00t!)', '(wOOt)' ),
        'whistling.png' => array( ':-"' ),
        'love.png'      => array( ':-X', ':-xX', ':x', '(wubya)', '(wubyou)', '(wub)' )
    );
    
    private function __construct()
    {}
    
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
    
    public function replaceSymbols( $text )
    {
        foreach( $this->_emoticons as $icon => $symbols )
        {
            $img = '<img src="'
                  . self::EMOTICONS_DIR
                  . $icon
                  . '" alt="' . substr( $icon, 0, -4 ) . '" />';
            
            foreach( $symbols as $symbol )
            {
            
                $symbol = str_replace( '\\', '\\\\', $symbol);
                $symbol = str_replace( '(', '\(', $symbol);
                $symbol = str_replace( ')', '\)', $symbol);
                $symbol = str_replace( '^', '\^', $symbol);
                $symbol = str_replace( '.', '\.', $symbol);
                $symbol = str_replace( '*', '\*', $symbol);
                $symbol = str_replace( '|', '\|', $symbol);
                
                $text = preg_replace(
                    '#\s' . $symbol . '\s#',
                    $img,
                    $text
                );
                
                $text = preg_replace(
                    '#\s' . $symbol . '$#',
                    $img,
                    $text
                );
                
                $text = preg_replace(
                    '#^' . $symbol . '\s#',
                    $img,
                    $text
                );
            }
        }
        
        return $text;
    }
}
