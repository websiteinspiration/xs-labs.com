<h2>Using boolean data-types with ANSI-C</h2>
<hr />
<p>
    <strong>Author:</strong> Jean-David Gadina<br />
    <strong>Copyright:</strong> &copy; <?php print date( 'Y', time() ); ?> Jean-David Gadina - www.xs-labs.com - All Rights Reserved<br />
    <strong>License:</strong> This article is published under the terms of the <?php print  XS_Menu::getInstance()->getPageLink( '/licenses/freebsd-documentation' ); ?><br />
</p>
<hr />
<p>
    Boolean data types are certainly the most often used data-type in any programming language.<br />
    They are the root of any programming logic.<br />
    Nowadays, few people remember that the boolean data type wasn't defined with the ANSI (C89) C programming language.
</p>
<p>
    It was added as part of the ISO-C99 standard, with the «stdbool.h» header file.
</p>
<p>
    Before this, it was up to each programmer to define its own boolean type, usually an enum, like the following one:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">typedef enum</span> { false = <span class="code-num">0</span>, true  = <span class="code-num">1</span> } bool;</code>
</div>
<p>
    Of course, unless using prefixes, such declarations may cause many problems, especially when using libraries, in which each programmer defined a boolean datatype.
</p>
<p>
    The ISO-C99 specification defined a «bool» datatype, defined in the «stdbool.h» header file.<br />
    That's great, but how can we be sure that we are coding for C99, what about code portability with old systems?
</p>
<p>
    The best way to ensure backward compatibility is to declare the boolean data-type exactly the same way C99 does.<br />
    A macro, named «__bool_true_false_are_defined» is specified, so you can know if the boolean data-type is actually declared and available.<br />
    No surprise, the «true» value must be defined to 1, and «false» to 0.
</p>
<p>
    In C99, the «bool» data-type must expand to «_Bool». If it's not defined, you can rely on on other data-type, like «int» or «char».
</p>
<p>
    The final declaration may look like this, to ensure a maximum portability and compatibility:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">#ifndef</span> __bool_true_false_are_defined</code><br />
    <code class="source"><span class="code-keyword">    #ifdef</span> _Bool</code><br />
    <code class="source"><span class="code-keyword">        #define</span> bool                        _Bool</code><br />
    <code class="source"><span class="code-keyword">    #else</span></code><br />
    <code class="source"><span class="code-keyword">        #define</span> bool                        <span class="code-keyword">int</span></code><br />
    <code class="source"><span class="code-keyword">    #endif</span></code><br />
    <code class="source"><span class="code-keyword">    #define</span> true                            <span class="code-num">1</span></code><br />
    <code class="source"><span class="code-keyword">    #define</span> false                           <span class="code-num">0</span></code><br />
    <code class="source"><span class="code-keyword">    #define</span> __bool_true_false_are_defined   <span class="code-num">1</span></code><br />
    <code class="source"><span class="code-keyword">#endif</span></code>
</div>
