<h2>Closure and anonymous functions in Objective-C</h2>
<hr />
<p>
    <strong>Author:</strong> Jean-David Gadina<br />
    <strong>Copyright:</strong> &copy; <?php print date( 'Y', time() ); ?> Jean-David Gadina - www.xs-labs.com - All Rights Reserved<br />
    <strong>License:</strong> This article is published under the terms of the <?php print  XS_Menu::getInstance()->getPageLink( '/licenses/freebsd-documentation' ); ?><br />
</p>
<hr />
<h3>Table of contents</h3>
<ol>
    <li>
        <a href="#about" title="Go to this section">About</a>
        <ol>
            <li>
                <a href="#about-lambda" title="Go to this section">Anonymous functions</a>
            </li>
            <li>
                <a href="#about-closure" title="Go to this section">Closure</a>
            </li>
        </ol>
    </li>
    <li>
        <a href="#objc" title="Go to this section">Objective-C implementation</a>
        <ol>
            <li>
                <a href="#objc-argument" title="Go to this section">Passing a block as an argument</a>
            </li>
            <li>
                <a href="#objc-closure" title="Go to this section">Closure</a>
            </li>
            <li>
                <a href="#objc-memory" title="Go to this section">Memory management</a>
            </li>
            <li>
                <a href="#objc-example" title="Go to this section">Examples</a>
            </li>
        </ol>
    </li>
</ol>
<a name="about"></a>
<h3>1. About</h3>
<p>
    Many scripting languages allows the use of «lambda» or «anonymous functions». That concept is often associated with the «closure» concept.<br />
    Such concepts are well known in JavaScript, ActionScript or PHP since version 5.3.<br />
    The Objective-C language offers an implementation of both concepts, called «blocks».<br />
    Blocks are available since Mac OS X 10.6, thanks to the use of Clang.
</p>
<a name="about-lambda"></a>
<h4>Anonymous functions</h4>
<p>
    As it name implies, an anonymous function is a function without a name, nor identifier. It only has its content (body), and can be stored in a variable, for a later use, or to be passed as an argument to another function.
</p>
<p>
    This concept is often used in scripting langauges for callbacks.
</p>
<p>
    In JavaScript, for instance, let's take a standard function called «foo», taking a callback as parameter, and executing it:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">function</span> foo( callback )</code><br />
    <code class="source">{</code><br />
    <code class="source">    callback();</code><br />
    <code class="source">}</code>
</div>
<p>
    It is possible to define another standard function, and pass it as argument of the first function:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">function</span> bar()</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">alert</span>( <span class="code-string">'hello, world'</span> );</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-ctag">foo</span>( bar );</code>
</div>
<p>
    The problem is that we are declaring a «bar» function in the global scope. So we risk to override another function having the same name.
</p>
<p>
    The JavaScript language allows us to declare the callback function at call time:
</p>
<div class="code">
    <code class="source">foo</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">function</span>()</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-predefined">alert</span>( <span class="code-string">'hello, world'</span> );</code><br />
    <code class="source">    }</code><br />
    <code class="source">);</code>
</div>
<p>
    Here, the callback has no identifier. It won't exist in the global scope, so it can't conflict with another existing function.
</p>
<p>
    We can also define the callback as a variable. It still won't exist in the global scope, but it will be possible to re-use it through the variable:
</p>
<div class="code">
    <code class="source">myCallback = <span class="code-keyword">function</span>()</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">alert</span>( <span class="code-string">'hello, world'</span> );</code><br />
    <code class="source">};</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-ctag">foo</span>( myCallback );</code>
</div>
<a name="about-closure"></a>
<h4>Closure</h4>
<p>
    The closure concept consist of the possibility for a function to access the variables available in its declaration context, even if it's not the same as it's execution context.
</p>
<p>
    In JavaScript again, let's see the following code:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">function</span> foo( callback )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">alert</span>( callback() );</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">function</span> bar()</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">var</span> str = <span class="code-string">'hello, world'</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-ctag">foo</span></code><br />
    <code class="source">    (</code><br />
    <code class="source">        <span class="code-keyword">function</span>()</code><br />
    <code class="source">        {</code><br />
    <code class="source">            <span class="code-keyword">return</span> str;</code><br />
    <code class="source">        }</code><br />
    <code class="source">    );</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-ctag">bar</span>();</code>
</div>
<p>
    The callback, passed to the «foo» function from the execution context of the «bar» function, returns a variable named «str».<br />
    But this variable, declared in the «bar» function's context, is local. It means it exists only from inside that context.<br />
    As the callback is executed from a different context than the one containing the variable declaration, we might think the code does not display anything.<br />
    But here comes the closure concept.<br />
    Even if its execution context is different, a function keeps an access to the variables avaialable in its declaration context.
</p>
<p>
    So the callback will have access to the «str» variable, even if it's called from the «foo» function, which does not have access to the variable.
</p>
<a name="objc"></a>
<h3>Objective-C implementation</h3>
<p>
    That kind of concept is available in Objective-C, with some differences, as Objective-C is a strongly typed compiled languaged, built on top of the C language, so very different of an interpreted scripting language.
</p>
<p>
    Note that blocks are also available in pure C, or C++ (and of course also in Objective-C++).
</p>
<p>
    As a standard C function, the declaration of a block (anonymous function) must be preceded by the declaration of its prototype.
</p>
<p>
    The syntax of a block is a bit tricky at first sight, but as function pointers, we get used to it with some time.<br />
    Here's a block prototype:
</p>
<div class="code">
    <code class="source"><span class="code-predefined">NSString</span> * ( ^ myBlock )( <span class="code-keyword">int</span> );</code>
</div>
<p>
    We are declaring here the prototype of a block («^»), that will be named «myBlock», taking as unique parameter an «int», et returning a pointer on a «NSString» object.
</p>
<p>
    Now we can declare the block itself:
</p>
<div class="code">
    <code class="source">myBlock = ^( <span class="code-keyword">int</span> number )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-keyword">return</span> [ <span class="code-predefined">NSString stringWithFormat</span>: <span class="code-string">@"Passed number: %i"</span>, number ];</code><br />
    <code class="source">};</code>
</div>
<p>
    We assign to the «myBlock» variable a function's body, taking an integer as the «number» argument. This function returns a «NSString» object, in which the integer will be displayed.
</p>
<p>
    <strong>Notice: do not forget the semicolon at the end of the block declaration!</strong>
</p>
<p>
    If it can be ommited in scripting langauges, it's absolutely necessary for compiled languages like Objective-C.<br />
    If it's ommited, the compiler will produce an error, and the final executable won't be generated.
</p>
<p>
    The block can now be used, as a standard function:
</p>
<div class="code">
    <code class="source">myBlock();</code>
</div>
<p>
    Here's the complete source code of an Objective-C program, with the previous example:
</p>
<div class="code">
    <code class="source"><span class="code-pre">#import</span> <span class="code-string">&lt;Cocoa/Cocoa.h&gt;</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">int</span> main( <span class="code-keyword">void</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSAutoreleasePool</span> * pool;</code><br />
    <code class="source">    <span class="code-predefined">NSString</span> * ( ^ myBlock )( <span class="code-keyword">int</span> );</code><br />
    <code class="source">    </code><br />
    <code class="source">    pool    = [ [ <span class="code-predefined">NSAutoreleasePool</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];</code><br />
    <code class="source">    myBlock = ^( <span class="code-keyword">int</span> number )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-keyword">return</span> [ <span class="code-predefined">NSString stringWithFormat</span>: <span class="code-string">@"Passed number: %i"</span>, number ];</code><br />
    <code class="source">    };</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-predefined">NSLog</span>( <span class="code-string">@"%@"</span>, myBlock() );</code><br />
    <code class="source">    </code><br />
    <code class="source">    [ pool <span class="code-predefined">release</span> ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> <span class="code-predefined">EXIT_SUCCESS</span>;</code><br />
    <code class="source">}</code>
</div>
<p>
    Such a program can be compiled with the following command (Terminal):
</p>
<div class="code">
    <code class="source">gcc -Wall -framework Cocoa -o test test.m</code>
</div>
<p>
    It will generate an executable named «test», from the «test.m» source file.<br />
    To launch the executable:
</p>
<div class="code">
    <code class="source">./test</code>
</div>
<p>
    The declaration of a block prototype can be ommited if the block is not assigned to a variable. For instance, if it's passed directly as a parameter.
</p>
<p>
    For instance:
</p>
<div class="code">
    <code class="source"><span class="code-ctag">someFunction</span>( ^ <span class="code-predefined">NSString</span> * ( <span class="code-keyword">void</span> ) { <span class="code-keyword">return</span> <span class="code-string">@"hello, world"</span> } );</code>
</div>
<p>
    Note that in such a case, the return type must be declared. Here, it's a «NSString» object.
</p>
<a name="objc-argument"></a>
<h4>Passing a block as a parameter</h4>
<p>
    A block can of course be passed as an argument of a C function.<br />
    Here again, the syntax is a bit tricky at first sight:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">void</span> logBlock( <span class="code-predefined">NSString</span> * ( ^ theBlock )( <span class="code-keyword">int</span> ) )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSLog</span>( <span class="code-string">@"Block returned: %@"</span>, theBlock() );</code><br />
    <code class="source">}</code>
</div>
<p>
    Of course, as Objective-C is a strongly typed language, a function taking a block as argument must also declare it's return type and the type of it's arguments, if any.
</p>
<p>
    Same thing for an objective-C method:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )logBlock: ( <span class="code-predefined">NSString</span> * ( ^ )( <span class="code-keyword">int</span> ) )theBlock;</code>
</div>
<a name="objc-closure"></a>
<h4>Closure</h4>
<p>
    The closure concept is also available in Objective-C, even if its behaviour is of course different than in interpreted languages.
</p>
<p>
    Let's see the following program:
</p>
<div class="code">
    <code class="source"><span class="code-pre">#import</span> <span class="code-string">&lt;Cocoa/Cocoa.h&gt;</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">void</span> logBlock( <span class="code-keyword">int</span> ( ^ theBlock )( <span class="code-keyword">void</span> ) )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSLog</span>( <span class="code-string">@"Closure var X: %i"</span>, theBlock() );</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">int</span> main( <span class="code-keyword">void</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSAutoreleasePool</span> * pool;</code><br />
    <code class="source">    <span class="code-keyword">int</span> ( ^ myBlock )( <span class="code-keyword">void</span> );</code><br />
    <code class="source">    <span class="code-keyword">int</span> x;</code><br />
    <code class="source">    </code><br />
    <code class="source">    pool = [ [ <span class="code-predefined">NSAutoreleasePool</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];</code><br />
    <code class="source">    x    = <span class="code-num">42</span>;</code><br />
    <code class="source">    </code><br />
    <code class="source">    myBlock = ^( <span class="code-keyword">void</span> )</code><br />
    <code class="source">    {</code><br />
    <code class="source">        <span class="code-keyword">return</span> x;</code><br />
    <code class="source">    };</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-ctag">logBlock</span>( myBlock );</code><br />
    <code class="source">    </code><br />
    <code class="source">    [ pool <span class="code-predefined">release</span> ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> <span class="code-predefined">EXIT_SUCCESS</span>;</code><br />
    <code class="source">}</code>
</div>
<p>
    The «main» function declares an integer, with 42 as value, and a block, returning that variable.<br />
    The block is then passed to the «logBlock» function, that will display its return value.
</p>
<p>
    Even in the execution context of the «logBlock» function, the block, declared in the «main» function, still has access to the «x» integer, and is able to return its value.
</p>
<p>
    Note that blocks also have access to global variable, even the static ones, if they are available in the block declaration context.
</p>
<p>
    Here comes a first difference. The variables available in a block by closure are typed as «const». It means their values can't be modified from inside the block.
</p>
<p>
    For instance, let's see what happen when our block tries to increment the value of «x»:
</p>
<div class="code">
    <code class="source">myBlock = ^( <span class="code-keyword">void</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    x++</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> x;</code><br />
    <code class="source">};</code>
</div>
<p>
    The compiler will produce an error, as the «x» variable is only available to read from inside the block.
</p>
<p>
    To allow a block to modify a variable, it has to be declared with the «__block» keyword.<br />
    The previous code is valid if we declare the «x» variable in the following way:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">__block</span> <span class="code-keyword">int</span> x;</code>
</div>
<a name="objc-memory"></a>
<h4>Memory management</h4>
<p>
    At the C level, a block is a structure, which can be copied or destroyed.<br />
    Two C functions are available for that use: «Block_copy()» and «Block_destroy()».<br />
    In Objective-C, a block can also receive the «retain», «release» and «copie» messages, as a normal object.
</p>
<p>
    It's extremely important if a block must be stored for a later use (for instance, stored in a class instance variable).
</p>
<p>
    In such a case, the block must be retained, in order to avoid a segmentation fault.
</p>
<a name="objc-example"></a>
<h4>Examples</h4>
<p>
    Blocks can be used in a lot of different contexts, to keep the code simple and to reduce the number of declared functions.
</p>
<p>
    Here's an example:
</p>
<p>
    We are going to add to the «NSArray» class a static method (class method) that will generate an array by filtering the items of another array, by a callback.
</p>
<p>
    For the PHP programmers, it's the same as the «array_filter()» function.
</p>
<p>
    First, we need to declare a category for the «NSArray» class.<br />
    A category allows to add methods to existing classes.
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@interface</span> <span class="code-predefined">NSArray</span>( BlockExample )</code><br />
    <code class="source"></code><br />
    <code class="source">+ ( <span class="code-predefined">NSArray</span> * )arrayByFilteringArray: ( <span class="code-predefined">NSArray</span> * )source withCallback: ( <span class="code-keyword">BOOL</span> ( ^ )( <span class="code-keyword">id</span> ) )callback;</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    Here, we are declaring a method returning a «NSArray» object, and taking as parameter another «NSArray» object, and a callback, as a block.
</p>
<p>
    The callback will be executed for each item of the array which is passed as parameter.<br />
    It will return a boolean value, in order to know if the current array item must be stored or not in the returned array.<br />
    The block takes as unique parameter an object, representing the current array item.
</p>
<p>
    Let's see the implementation of that method:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@implementation</span> <span class="code-predefined">NSArray</span>( BlockExample )</code><br />
    <code class="source"></code><br />
    <code class="source">+ ( <span class="code-predefined">NSArray</span> * )arrayByFilteringArray: ( <span class="code-predefined">NSArray</span> * )source withCallback: ( <span class="code-keyword">BOOL</span> ( ^ )( <span class="code-keyword">id</span> ) )callback</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSMutableArray</span> * result;</code><br />
    <code class="source">    <span class="code-keyword">id</span>               element;</code><br />
    <code class="source">    </code><br />
    <code class="source">    result = [ <span class="code-predefined">NSMutableArray</span> <span class="code-predefined">arrayWithCapacity</span>: [ source <span class="code-predefined">count</span> ] ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">for</span>( element <span class="code-keyword">in</span> source ) {</code><br />
    <code class="source">        </code><br />
    <code class="source">        <span class="code-keyword">if</span>( callback( element ) == <span class="code-keyword">YES</span> ) {</code><br />
    <code class="source">            </code><br />
    <code class="source">            [ result <span class="code-predefined">addObject</span>: element ];</code><br />
    <code class="source">        }</code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> result;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    First, we create an array with a dynamic size («NSMutableArray»). It's initial capacity is the same as the number of items of the source array.
</p>
<p>
    Then, we iterate through each item of the source array, and we add the current item if the result of the callback is the boolean value «YES».
</p>
<p>
    Here's a complete example of a program using such a method.<br />
    We are using the callback to create an array that contains only the items of type «NSString» from the source array:
</p>
<div class="code">
    <code class="source"><span class="code-pre">#import</span> <span class="code-string">&lt;Cocoa/Cocoa.h&gt;</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@interface</span> <span class="code-predefined">NSArray</span>( BlockExample )</code><br />
    <code class="source"></code><br />
    <code class="source">+ ( <span class="code-predefined">NSArray</span> * )arrayByFilteringArray: ( <span class="code-predefined">NSArray</span> * )source withCallback: ( <span class="code-keyword">BOOL</span> ( ^ )( <span class="code-keyword">id</span> ) )callback;</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@implementation</span> <span class="code-predefined">NSArray</span>( BlockExample )</code><br />
    <code class="source"></code><br />
    <code class="source">+ ( <span class="code-predefined">NSArray</span> * )arrayByFilteringArray: ( <span class="code-predefined">NSArray</span> * )source withCallback: ( <span class="code-keyword">BOOL</span> ( ^ )( <span class="code-keyword">id</span> ) )callback</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSMutableArray</span> * result;</code><br />
    <code class="source">    <span class="code-keyword">id</span>               element;</code><br />
    <code class="source">    </code><br />
    <code class="source">    result = [ <span class="code-predefined">NSMutableArray</span> <span class="code-predefined">arrayWithCapacity</span>: [ source <span class="code-predefined">count</span> ] ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">for</span>( element <span class="code-keyword">in</span> source ) {</code><br />
    <code class="source">        </code><br />
    <code class="source">        <span class="code-keyword">if</span>( callback( element ) == <span class="code-keyword">YES</span> ) {</code><br />
    <code class="source">            </code><br />
    <code class="source">            [ result <span class="code-predefined">addObject</span>: element ];</code><br />
    <code class="source">        }</code><br />
    <code class="source">    }</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> result;</code><br />
    <code class="source">}</code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">@end</span></code><br />
    <code class="source"></code><br />
    <code class="source"><span class="code-keyword">int</span> main( <span class="code-keyword">void</span> )</code><br />
    <code class="source">{</code><br />
    <code class="source">    <span class="code-predefined">NSAutoreleasePool</span> * pool;</code><br />
    <code class="source">    <span class="code-predefined">NSArray</span>           * array1;</code><br />
    <code class="source">    <span class="code-predefined">NSArray</span>           * array2;</code><br />
    <code class="source">    </code><br />
    <code class="source">    pool   = [ [ <span class="code-predefined">NSAutoreleasePool</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];</code><br />
    <code class="source">    array1 = [ <span class="code-predefined">NSArray</span> <span class="code-predefined">arrayWithObjects</span>: <span class="code-string">@"hello, world"</span>, [ <span class="code-predefined">NSDate</span> <span class="code-predefined">date</span> ], <span class="code-string">@"hello, universe"</span>, <span class="code-keyword">nil</span> ];</code><br />
    <code class="source">    array2 = [ <span class="code-predefined">NSArray</span></code><br />
    <code class="source">                    <span class="code-ctag">arrayByFilteringArray</span>: array1</code><br />
    <code class="source">                    <span class="code-ctag">withCallback</span>:          ^ <span class="code-keyword">BOOL</span> ( <span class="code-keyword">id</span> element )</code><br />
    <code class="source">                    {</code><br />
    <code class="source">                        <span class="code-keyword">return</span> [ element <span class="code-predefined">isKindOfClass</span>: [ <span class="code-predefined">NSString</span> <span class="code-predefined">class</span> ] ];</code><br />
    <code class="source">                    }</code><br />
    <code class="source">             ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-predefined">NSLog</span>( <span class="code-string">@"%@"</span>, array2 );</code><br />
    <code class="source">    </code><br />
    <code class="source">    [ pool <span class="code-predefined">release</span> ];</code><br />
    <code class="source">    </code><br />
    <code class="source">    <span class="code-keyword">return</span> <span class="code-predefined">EXIT_SUCCESS</span>;</code><br />
    <code class="source">}</code><br />
</div>
