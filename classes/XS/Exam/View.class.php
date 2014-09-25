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

class XS_Exam_View
{
    protected $_exam       = NULL;
    protected $_html       = NULL;
    protected $_lang       = NULL;
    protected $_session    = NULL;
    protected $_menu       = NULL;
    protected $_question   = NULL;
    protected $_questions  = array();
    protected $_end        = false;
    protected $_position   = 0;
    protected $_percent    = 0;
    
    public function __construct( $id )
    {
        $this->_html            = new XS_Xhtml_Tag( 'div' );
        $this->_html[ 'class' ] = 'exam';
        $this->_lang            = XS_Language_File::getInstance( __CLASS__ );
        $this->_session         = XS_Session::getInstance();
        $this->_menu            = XS_Menu::getInstance();
        $this->_exam            = new XS_Database_Object( 'EXAMS', ( int )$id );
        
        if( !$this->_exam )
        {
            return;
        }
        
        $questions = XS_Database_Object::getObjectsByFields
        (
            'EXAM_QUESTIONS',
            array
            (
                'id_exam' => $this->_exam->getId()
            ),
            'position ASC'
        );
        
        if( !is_array( $questions ) || !count( $questions ) )
        {
            return;
        }
        else
        {
            $this->_questions = array_values( $questions );
        }
        
        if( isset( $_POST[ 'question' ] ) )
        {
            $this->_position = ( int )$_POST[ 'question' ] + 1;
        }
        
        if( $this->_position === count( $this->_questions ) )
        {
            $this->_end = true;
        }
        else
        {
            $this->_question = $this->_questions[ $this->_position ];
        }
        
        if( ( !isset( $this->_session->examId ) || $this->_session->examId != $this->_exam->getId() ) ||( $this->_position === 0 && isset( $this->_session->examEnded ) && $this->_session->examEnded == 1 ) )
        {
            $data                      = array();
            $this->_session->examId    = $this->_exam->getId();
            $this->_session->startTime = time();
            $this->_session->examEnded = 0;
        }
        else
        {
            $data = ( !isset( $this->_session->exam ) || !is_array( $this->_session->exam ) ) ? array() : $this->_session->exam;
        }
        
        if( isset( $_POST[ 'answer' ] ) && is_array( $_POST[ 'answer' ] ) && isset( $this->_questions[ $this->_position - 1 ] ) )
        {
            $question                   = $this->_questions[ $this->_position - 1 ];
            $data[ $question->getId() ] = implode( ':', $_POST[ 'answer' ] );
        }
        elseif( isset( $_POST[ 'question' ] ) && isset( $this->_questions[ ( int )$_POST[ 'question' ] ] ) )
        {
            $question                   = $this->_questions[ ( int )$_POST[ 'question' ] ];
            $data[ $question->getId() ] = '';
        }
        
        $this->_session->exam = $data;
    }
    
    public function __toString()
    {
        try
        {
            if( !$this->_exam )
            {
                $this->_html->addTextData( $this->_lang->noExam );
            }
            else
            {
                $this->_html->h2 = htmlspecialchars( $this->_exam->title );
                
                if( $this->_end === true )
                {
                    $this->_displayResults();
                    $this->_storeResults();
                }
                elseif( $this->_question )
                {
                    $this->_displayQuestion();
                }
                else
                {
                    $this->_html->addTextData( $this->_lang->noQuestion );
                }
            }
            
            return ( string )$this->_html;
        }
        catch( Exception $e )
        {
            return $e->getMessage();
        }
    }
    
    protected function _displayQuestion()
    {
        $this->_html->h3 = '<span>'
                         . ( $this->_position + 1 ) . ' / ' . count( $this->_questions )
                         . '</span>'
                         . ' - ' . htmlspecialchars( $this->_question->title );
        
        if( !empty( $this->_question->code ) )
        {
            $code            = $this->_html->div;
            $code[ 'class' ] = 'code';
            $source          = '';
            
            $lines = explode( chr( 10 ), XS_String_Utils::getInstance()->unifyLineBreaks( $this->_question->code ) );
            
            foreach( $lines as $line )
            {
                $source .= '<code class="source">' . htmlspecialchars( $line ) . '</code><br />' . chr( 10 );
            }
            
            $code->addTextData( $source );
        }
            
        $form                = $this->_html->form;
        $form[ 'method' ]    = 'post';
        $form[ 'name' ]      = __CLASS__;
        $form[ 'id' ]        = __CLASS__;
        $form[ 'action' ]    = $this->_menu->getCurrentPageUrl();
        $question            = $form->input;
        $question[ 'type' ]  = 'hidden';
        $question[ 'name' ]  = 'question';
        $question[ 'value' ] = $this->_position;
        
        if( !empty( $this->_question->note ) )
        {
            $note               = $form->div;
            $note->h4           = $this->_question->note;
            $noteText           = $note->div->input;
            $noteText[ 'type' ] = 'text';
            $noteText[ 'size' ] = '50';
            $noteText[ 'name' ] = 'answer[]';
        }
        else
        {
            $answers = $form->div;
            $data    = $this->_session->exam;
            
            for( $i = 1; $i < 16; $i++ )
            {
                $choice = 'choice_' . $i;
                
                if( $this->_question->$choice !== '0' && !$this->_question->$choice )
                {
                    break;
                }
                
                $answer              = $answers->div;
                $answer[ 'class' ]   = ( ( $i - 1 ) % 2 ) ? 'choice-even' : 'choice-odd';
                
                if( !isset( $data[ $this->_question->getId() ] ) )
                {
                    $checkbox            = $answer->input;
                    $label               = $answer->label;
                    $checkbox[ 'type' ]  = 'checkbox';
                    $checkbox[ 'id' ]    = 'choice_' . $i;
                    $checkbox[ 'name' ]  = 'answer[]';
                    $checkbox[ 'value' ] = $i;
                    $label[ 'for' ]      = 'choice_' . $i;
                    
                    $label->addTextData( htmlspecialchars( $this->_question->$choice ) );
            
                }
                else
                {
                    $answer->addTextData( htmlspecialchars( $this->_question->$choice ) );
                    
                    $results = explode( ':', $data[ $this->_question->getId() ] );
                    
                    if( in_array( $i, $results ) )
                    {
                        $answer[ 'class' ] = 'choice-selected';
                    }
                }
            }
        }
        
        $next              = $form->div;
        $next[ 'class' ]   = 'submit';
        $submit            = $next->input;
        $submit[ 'type' ]  = 'submit';
        $submit[ 'value' ] = ( $this->_position < ( count( $this->_questions ) - 1 ) ) ? $this->_lang->next : $this->_lang->end;
    }
    
    protected function _storeResults()
    {
        $result             = new XS_Database_Object( 'EXAM_RESULTS' );
        $result->id_exam    = $this->_exam->getId();
        $result->session    = $this->_session->getId();
        $result->percent    = $this->_percent;
        
        if( isset( $this->_session->startTime ) )
        {
            $result->spent_time = time() - ( int )( $this->_session->startTime );
        }
        
        if( isset( $_SERVER[ 'REMOTE_ADDR' ] ) ) {
            
            $result->ip = $_SERVER[ 'REMOTE_ADDR' ];
        }
        
        $result->commit();
        
        $data = ( isset( $this->_session->exam ) && is_array( $this->_session->exam ) ) ? $this->_session->exam : array();
        
        foreach( $this->_questions as $question )
        {
            $answer                   = new XS_Database_Object( 'EXAM_ANSWERS' );
            $answer->id_exam_result   = $result->getId();
            $answer->id_exam_question = $question->getId();
            
            if( isset( $data[ $question->getId() ] ) )
            {
                if( !empty( $question[ 'note' ] ) )
                {
                    $answer->note = ( string )( $data[ $question->getId() ] );
                }
                else
                {
                    $choices = explode( ':', $data[ $question->getId() ] );
                    
                    foreach( $choices as $choice )
                    {
                        $key = 'choice_' . $choice;
                        
                        if( isset( $question->$key ) )
                        {
                            $answer->$key = 1;
                        }
                    }
                }
            }
            
            $answer->commit();
        }
        
        $this->_session->examEnded = 1;
    }
    
    protected function _displayResults()
    {
        $results = ( isset( $this->_session->exam ) && is_array( $this->_session->exam ) ) ? $this->_session->exam : array();
        $total   = 0;
        $valid   = 0;
        
        $score              = $this->_html->div;
        $score[ 'class' ]   = 'score';
        $percents           = $score->h3;
        $twitter            = $score->div;
        $twitter[ 'class' ] = 'share-twitter';
        $twitterLink        = $twitter->a;
        $details            = $this->_html->div;
        $details[ 'class' ] = 'details';
        $details->h3        = $this->_lang->details;
        
        $i     = 1;
        $count = count( $this->_questions );
        
        foreach( $this->_questions as $question )
        {
            $answers = explode( ':', $question->answer );
            $result  = ( isset( $results[ $question->getId() ] ) ) ? explode( ':', $results[ $question->getId() ] ) : array();
            $total  += count( $answers );
            $success = -1;
            
            foreach( $result as $value )
            {
                if( in_array( $value, $answers ) )
                {
                    $valid++;
                    
                    $success = ( $success !== 0 ) ? 1 : 0;
                    
                }
                else
                {
                    $valid--;
                    
                    $success = 0;
                }
            }
            
            if( count( $answers ) !== count( $result ) )
            {
                $success = 0;
            }
            
            if( $success === 1 )
            {
                $i++;
                continue;
            }
            
            $detail            = $details->div;
            $detail[ 'class' ] = 'detail';
            $title             = $detail->h5;
            $title[ 'class' ]  = ( $success === true ) ? 'success' : 'failure';
            
            $title->addTextData( '<span>' . $i . ' / ' . $count . '</span> - ' . htmlspecialchars( $question->title ) );
            
            if( !empty( $question->code ) )
            {
                $code            = $detail->div;
                $code[ 'class' ] = 'code';
                $source          = '';
                
                $lines = explode( chr( 10 ), XS_String_Utils::getInstance()->unifyLineBreaks( $question->code ) );
                
                foreach( $lines as $line )
                {
                    $source .= '<code class="source">' . htmlspecialchars( $line ) . '</code><br />' . chr( 10 );
                }
                
                $code->addTextData( $source );
            }
            
            if( !empty( $question->note ) )
            {
                $detail->h6        = htmlspecialchars( $question->note );
                $choice            = $detail->div;
                $choice[ 'class' ] = 'choice-odd-success';
                
                $choice->addTextData( htmlspecialchars( $question->answer ) );
            }
            else
            {
                for( $j = 1; $j < 16; $j++ )
                {
                    $choiceKey = 'choice_' . $j;
                    
                    if( $question->$choiceKey !== '0' && !$question->$choiceKey )
                    {
                        break;
                    }
                    
                    $choice              = $detail->div;
                    $choice[ 'class' ]   = ( ( $j - 1 ) % 2 ) ? 'choice-even' : 'choice-odd';
                    
                    if( in_array( $j, $answers ) )
                    {
                        $choice[ 'class' ] .= '-success';
                    }
                    
                    $choice->addTextData( htmlspecialchars( $question->$choiceKey ) );
                }
            }
            
            $userAnswers = array();
            
            foreach( $result as $userAnswer )
            {
                if( $question->note )
                {
                    if( !empty( $userAnswer ) )
                    {
                        $userAnswers[] = $userAnswer;
                    }
                }
                elseif( $userAnswer )
                {
                    $choiceKey = 'choice_' . $userAnswer;
                    
                    $userAnswers[] = $question->$choiceKey;
                }
            }
            
            if( count( $userAnswers ) )
            {
                $detail->h6 = $this->_lang->yourAnswered;
                $list       = $detail->ul;
                
                foreach( $userAnswers as $userAnswer )
                {
                    $list->li = $userAnswer;
                }
            }
            
            if( $question->details )
            {
                $explaination            = $detail->div;
                $explaination[ 'class' ] = 'explaination';
                $explaination->h6        = $this->_lang->explaination;
                $explaination->div       = nl2br( htmlspecialchars( $question->details ) );
            }
            
            $i++;
        }
        
        $percent        = round( ( $valid / $total ) * 100 );
        $this->_percent = ( $percent < 0 ) ? 0 : $percent;
        
        $percents->addTextData( sprintf( $this->_lang->resultScore, '<span>' . $this->_percent . '%</span>' ) );
        $twitterLink->addTextData( $this->_lang->twitterShare );
        
        $twitterLink[ 'href' ] = $this->_getTwitterShareUrl();
    }
    
    protected function _getTwitterShareUrl()
    {
        $host     = ( ( isset( $_SERVER[ 'HTTPS' ] ) ) ? 'https://' : 'http://' )
                  . $_SERVER[ 'HTTP_HOST' ];
        $shortUrl = new XS_Bitly_Url_Shortener( 'macmade', 'R_15147e146b0669fc6100a61adb7a1fe5' );
        $infos    = explode( '/', $_SERVER[ 'REQUEST_URI' ] );
        
        array_shift( $infos );
        array_shift( $infos );
        array_pop( $infos );
        array_pop( $infos );
        
        $url    = $host . $this->_menu->getPageUrl( implode( '/', $infos ) );
        $status = sprintf
        (
            $this->_lang->twitterShareText,
            $this->_percent,
            $this->_exam->title,
            $shortUrl->shorten( $url )
        );
        
        return 'http://twitter.com/home?status=' . urlencode( $status );
    }
}
