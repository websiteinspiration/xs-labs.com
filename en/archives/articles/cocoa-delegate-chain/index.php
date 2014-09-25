<h2>Implementing a delegate chain system in Objective-C</h2>
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
        <a href="#delegation-about" title="Go to this section">What's delegation?</a>
    </li>
    <li>
        <a href="#delegation-explaination" title="Go to this section">How does delegation work?</a>
    </li>
    <li>
        <a href="#delegation-notification" title="Go to this section">Delegation and notification</a>
    </li>
    <li>
        <a href="#delegate-chain" title="Go to this section">Delegation chain</a>
    </li>
    <li>
        <a href="#implementation-object" title="Go to this section">Implementation - MultipleDelegateObject</a>
    </li>
    <li>
        <a href="#implementation-chain" title="Go to this section">Implementation - DelegateChain</a>
    </li>
    <li>
        <a href="#runtime" title="Go to this section">Runtime and method routing </a>
    </li>
    <li>
        <a href="#conclusion" title="Go to this section">Afterwords</a>
    </li>
</ol>
<a name="delegation-about"></a>
<h3>1. What's delegation?</h3>
<p>
    Delegation is a concept available in some classes of the Cocoa framework, on Mac OS X (and of course, on iPhone OS).<br />
    That concept allows Cocoa application developers to interact on specific events of core Cocoa objects.
</p>
<p>
    Let's take, for instance, the NSWindow object. As it name implies, it allows to display and control a window.
</p>
<p>
    This window object has methods, like 'close' or 'open', allowing respectively to open and clase the window.
</p>
<p>
    When developping a Cocoa application, it can be very useful to know when a window will open or close, to allocate or free resources, end tasks or threads, etc.
</p>
<p>
    The delegation system of the Cocoa framework allows to attach an object's instance to another object, the first one beeing able to act on the second depending on its execution phases.
</p>
<p>
    Defining a delegate object on another object is usually done with the 'setDelegate' method, taking as unique parameter the instance of the delegate object.
</p>
<p>
    For instance, to define an object of type 'Foo' as the delegate of a NSWindow object:
</p>
<div class="code">
    <code class="source"><span class="code-ctag">Foo</span>      * foo    = [ [ <span class="code-ctag">Foo</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];<br /></code>
    <code class="source"><span class="code-predefined">NSWindow</span> * window = [ [ <span class="code-predefined">NSWindow</span> alloc ] <span class="code-predefined">initWithContentRect</span>: <span class="code-predefined">NSMakeRect</span>( <span class="code-num">0</span>, <span class="code-num">0</span>, <span class="code-num">100</span>, <span class="code-num">100</span> ) <span class="code-predefined">styleMask</span>: <span class="code-predefined">NSTitledWindowMask</span> <span class="code-predefined">backing</span>: <span class="code-predefined">NSBackingStoreBuffered</span> <span class="code-predefined">defer</span>: <span class="code-keyword">NO</span> ];<br /></code>
    <code class="source"><br /></code>
    <code class="source">[ window <span class="code-predefined">setDelegate</span>: foo ];</code>
</div>
<p>
    The two first lines respectively creates an object of type 'Foo' (defined in our application), and an object of type 'NSWindow' (from the Cocoa framework).
</p>
<p>
    The third line defines the 'Foo' object as a delegate of our 'NSWindow' object.
</p>
<p>
    From now on, if we close the window object.
</p>
<div class="code">
    <code class="source">[ window <span class="code-predefined">close</span> ];</code>
</div>
<p>
    The delegate object can be noticed of the close operation by implementing a specific method. In our case, the 'windowWillClose' method. Here's the method's prototype:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )windowWillClose: ( <span class="code-predefined">NSNotification</span> * )notification;</code>
</div>
<p>
    This will allow the delegate, just before the window closes, to perform operations required by the application.
</p>
<a name="delegation-explaination"></a>
<h3>2. How does delegation work?</h3>
<p>
    Now, let's see how the 'NSWindow' object implements and uses its delegate object.
</p>
<p>
    The 'NSWindow' object has of course an instance variable of type 'id', representing the delegate object and usually named delegate, as well as getter/setter methods for the delegate object.
</p>
<p>
    In other words:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@interface</span> <span class="code-predefined">NSWindow</span>: <span class="code-predefined">NSObject</span><br /></code>
    <code class="source">{<br /></code>
    <code class="source"><span class="code-keyword">@protected</span><br /></code>
    <code class="source"><br /></code>
    <code class="source">    <span class="code-keyword">id</span> _delegate;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">id</span> )delegate;<br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )setDelegate: ( <span class="code-keyword">id</span> )object;<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span><br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@implementation</span> <span class="code-predefined">NSWindow</span><br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">id</span> )delegate<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-keyword">return</span> <span class="code-ctag">_delegate</span>;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )setDelegate: ( <span class="code-keyword">id</span> )object<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-ctag">delegate</span> = _object;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    It's very important to remember that an object should never retain its delegate object, as this would result in a memory leak (a memory area that will never be freed).
</p>
<p>
    If using Objective-C 2.0, note that you can use a property in the interface declaration to allow an easy acces to the delegate object:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@property</span>( <span class="code-keyword">nonatomic</span>, <span class="code-keyword">assign</span>, <span class="code-keyword">readwrite</span> ) delegate;</code>
</div>
<p>
    From that point, the getter/setter methods can be automatically declared in the implementation:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@synthesize</span> delegate;</code>
</div>
<p>
    Now, back to the 'close' method of the 'NSWindow' object:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )close<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-comment">/* Do something... */</span><br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( [ <span class="code-ctag">_delegate</span> <span class="code-predefined">respondsToSelector</span>: <span class="code-keyword">@selector</span>( <span class="code-predefined">windowWillClose</span> ) ] )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        [ <span class="code-ctag">_delegate</span> <span class="code-predefined">windowWillClose</span> ];<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-comment">/* Do something... */</span><br /></code>
    <code class="source">}</code>
</div>
<p>
    At a specific time during the execution of the 'close' method, the 'NSWindow' object checks if delegate object implements a method named 'windowWillClose'.<br />
    If it has, it's executed. The 'close' method then continues its own execution.
</p>
<p>
    The 'close' method does not need to check if a delegate object has been previously defined, as it is valid in Objective-C to send a message (call a method) to 'nil' (a NULL pointer on an object).
</p>
<p>
    The delegate object will then be notified that the 'NSWindow' object did close, if it implements the 'windowWillClose' method.
</p>
<a name="delegation-notification"></a>
<h3>3. Delegation and notification</h3>
<p>
    The Cocoa framework also include a notification system, allowing objects to be notified about execution stages of other objects.
</p>
<p>
    In the previous example, we could also have written the following code, to be noticed about the window's close event:
</p>
<div class="code">
    <code class="source">[ [ <span class="code-predefined">NSNotificationCenter</span> <span class="code-predefined">defaultCenter</span> ] <span class="code-predefined">addObserver</span>: foo <span class="code-predefined">selector</span>: <span class="code-keyword">@selector</span>:( <span class="code-ctag">myObserverMethod:</span> ) <span class="code-predefined">name:</span> <span class="code-predefined">NSWindowWillCloseNotification</span> <span class="code-predefined">object</span>: window ]:</code>
</div>
<p>
    In other words, we declare that the 'myObserverMethod' method of the 'Foo' object must be called when the window's 'NSWindowWillCloseNotification' event occurs. In such a case, here's the prototype of the 'myObserverMethod' method:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )myObserverMethod: ( <span class="code-predefined">NSNotification</span> * )notification;</code>
</div>
<p>
    So what are the differences between those two methodologies?<br />
    The notification system only allows to be notified about some events, while the delegation system also allows to modify the behaviour of the concerned object.
</p>
<p>
    Let's take the 'windowShouldClose' method as an example. It can be implemented in the delegate of a 'NSWindow' object, and here's it's prototype:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">BOOL</span> )windowShouldClose: ( <span class="code-predefined">NSWindow</span> * )window;</code>
</div>
<p>
    We can here see that the method returns a boolean value.
</p>
<p>
    If our delegate object does implement this method, and if we call the 'close' method on the window, it will effectively close only if the delegate's method returns the 'YES' value. If not, the window will stay on the screen.
</p>
<p>
    This allows, for instance, to block the window's close process to display an alert message, asking the user if he wants to save it's data before closing the window.
</p>
<p>
    At this time, the delagate object takes the responsibility to know if the window has to be closed, and when. This would be impossible through the notification system.
</p>
<p>
    We can clearly see here the difference of logic between delegation and notification.
</p>
<p>
    Some classes of the Cocoa framework also use their delegate to obtain other types of informations, like the 'NSBrowser' (the column view of the Finder), which uses its delegate to know which items to display.
</p>
<a name="delegate-chain"></a>
<h3>4. Chaining delegates</h3>
<p>
    At this time, we can notice a limitation of the delegation system: an object can only have one unique delegate.
</p>
<p>
    Let's take the following code:
</p>
<div class="code">
    <code class="source">[ window <span class="code-predefined">setDelegate</span>: foo ];<br /></code>
    <code class="source">[ window <span class="code-predefined">setDelegate</span>: bar ];</code>
</div>
<p>
    The delegate object of the 'window' object will be 'bar', which will override the 'foo' object, which won't be able to control the window anymore.
</p>
<p>
    Having multiple delegate objects could be useful in many cases, so we are going to implement a system allowing the delegates to be chained.
</p>
<a name="implementation-object"></a>
<h3>5. Implementation - MultipleDelegateObject</h3>
<p>
    First, we are going to create a base class for the classes needing multiple delegate objects:
</p>
<div class="code">
    <code class="source"><span class="code-comment">/* MultipleDelegateObject.h */</span><br /></code>
    <code class="source"><span class="code-keyword">@interface</span> MultipleDelegateObject: <span class="code-predefined">NSObject</span><br /></code>
    <code class="source">{<br /></code>
    <code class="source"><span class="code-keyword">@protected</span><br /></code>
    <code class="source"><br /></code>
    <code class="source">    <span class="code-ctag">DelegateChain</span> * <span class="code-ctag">_delegate</span>;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )addDelegate: ( <span class="code-keyword">id</span> )object;<br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )removeDelegate: ( <span class="code-keyword">id</span> )object;<br /></code>
    <code class="source">- ( <span class="code-predefined"><span class="code-predefined">NSArray</span></span> * )delegates;<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span>;</code>
</div>
<p>
    We won't manage the delegate chain here, but in another class, named 'DelegateChain'. We'll see this class in a few moments.
</p>
<p>
    Our first class has methods allowing a delegate object to be added or removed, and a method allowing to get all the delegates in an array.
</p>
<p>
    Here's the implementation:
</p>
<div class="code">
    <code class="source"><span class="code-comment">/* MultipleDelegateObject.m */</span><br /></code>
    <code class="source"><span class="code-keyword">@implementation</span><br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">id</span> )init<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-keyword">if</span>( ( <span class="code-keyword">self</span> = [ <span class="code-keyword">super</span> <span class="code-predefined">init</span> ] ) )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-ctag">_delegate</span> = [ [ <span class="code-ctag">DelegateChain</span> <span class="code-predefined">alloc</span> ] <span class="code-predefined">init</span> ];<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">return</span> <span class="code-keyword">self</span>;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )dealloc<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    [ <span class="code-ctag">_delegate</span> <span class="code-predefined">release</span> ];<br /></code>
    <code class="source">    [ <span class="code-keyword">super</span>     <span class="code-predefined">dealloc</span> ];<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )addDelegate: ( <span class="code-keyword">id</span> )object<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    [ <span class="code-ctag">_delegate</span> <span class="code-ctag">addDelegate</span>: object ];<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )removeDelegate: ( <span class="code-keyword">id</span> )object<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    [ <span class="code-ctag">_delegate</span> <span class="code-ctag">removeDelegate</span>: object ];<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-predefined"><span class="code-predefined">NSArray</span></span> * )delegates<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-keyword">return</span> [ <span class="code-ctag">_delegate</span> <span class="code-ctag">delegates</span> ];<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    The 'init' method creates a new instance of the 'DelegateChain' class and stores it in the 'delegate' instance variable. The 'dealloc' method releases this resource when the object is freed.
</p>
<p>
    The three other methods only route the calls to the 'DelegateChain' object, which will manage the multiple delegates.
</p>
<a name="implementation-chain"></a>
<h3>6. Implementation - DelegateChain</h3>
<p>
    Let's see the interface of the 'DelegateChain' class:
</p>
<div class="code">
    <code class="source"><span class="code-comment">/* DelegateChain.h */</span><br /></code>
    <code class="source"><span class="code-keyword">@interface</span> DelegateChain: <span class="code-predefined">NSObject</span><br /></code>
    <code class="source">{<br /></code>
    <code class="source"><span class="code-keyword">@protected</span><br /></code>
    <code class="source"><br /></code>
    <code class="source">    <span class="code-keyword">id</span>                  * _delegates;<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span>            _numberOfDelegates;<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span>            _sizeOfDelegatesArray;<br /></code>
    <code class="source">    <span class="code-predefined">NSMutableDictionary</span> * _hashs;<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )addDelegate: ( <span class="code-keyword">id</span> )object;<br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )removeDelegate: ( <span class="code-keyword">id</span> )object;<br /></code>
    <code class="source">- ( <span class="code-predefined">NSArray</span> * )delegates;<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    We've seen previously that we cannot retain a delegate object. So we cannot use an 'NSMutableArray' or 'NSMutableDictionary' object to store the delegates, as they would be automatically retained when added to the array or dictionary.
</p>
<p>
    But we can still use an array of pointers to the delegates (the 'id' type is in fact a pointer), allocated and re-allocated when necessary with the standard C library memory allocation functions. That's our 'delegates' instance variable.
</p>
<p>
    We also have a variable keeping the number of the associated delegates ('numberOfDelegates'), and another ('sizeOfDelegatesArray') keeping the size of the array of pointers.
</p>
<p>
    The 'hash' variable will be used to store the memory addresses of the delegate objects, so we'll be able to find their position easily in the array of pointers.
</p>
<p>
    Now let's see, method by method, the implementation of the 'DelegateChain' class. First of all, its initialization:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">id</span> )init<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-keyword">if</span>( ( <span class="code-keyword">self</span> = [ <span class="code-keyword">super</span> <span class="code-predefined">init</span> ] ) )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-ctag">_hashs</span> = [ [ <span class="code-predefined">NSMutableDictionary</span> <span class="code-predefined">dictionaryWithCapacity</span>: <span class="code-num">10</span> ] retain ];<br /></code>
    <code class="source">        <br /></code>
    <code class="source">        <span class="code-keyword">if</span>( <span class="code-keyword">NULL</span> = ( <span class="code-ctag">_delegates</span> = ( <span class="code-keyword">id</span> * )<span class="code-predefined">calloc</span>( <span class="code-num">10</span>, <span class="code-keyword">sizeof</span>( <span class="code-keyword">id</span> ) ) ) )<br /></code>
    <code class="source">        {<br /></code>
    <code class="source">            <span class="code-comment">/* Error management... */</span><br /></code>
    <code class="source">        }<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">return</span> <span class="code-keyword">self</span>;<br /></code>
    <code class="source">}</code>
</div>
<p>
    We create the dictionary which will store the memory addresses, and we ask for a memory area to store the pointers to the delegate objects. At the initialization time, this area can store 10 objects. We are doing this to improve the performances, as we won't need to call the memory allocation functions each time a delegate is added. If we need more than 10 delegates, we will increase this area so it can store 10 objects more.
</p>
<p>
    As we allocated memory, we need to free it when the object is deallocated:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )dealloc<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">free</span>( <span class="code-ctag">_delegates</span> );<br /></code>
    <code class="source">    </code><br />
    <code class="source">    [ <span class="code-ctag">_hashs</span> <span class="code-predefined">release</span> ];<br /></code>
    <code class="source">    [ <span class="code-keyword">super</span>  <span class="code-predefined">dealloc</span> ];<br /></code>
    <code class="source">}</code>
</div>
<p>
    Now let's see the method used to add a delegate:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )addDelegate: ( <span class="code-keyword">id</span> )object<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSString</span> * hash;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( object == <span class="code-keyword">nil</span> )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">return</span>;<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( <span class="code-ctag">_numberOfDelegates</span> == <span class="code-ctag">_sizeOfDelegatesArray</span> )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">if</span>( <span class="code-keyword">NULL</span> == ( <span class="code-ctag">_delegates</span> = ( <span class="code-keyword">id</span> * )<span class="code-predefined">realloc</span>( <span class="code-ctag">_delegates</span>, ( <span class="code-ctag">_sizeOfDelegatesArray</span> + <span class="code-num">10</span> ) * <span class="code-keyword">sizeof</span>( <span class="code-keyword">id</span> ) ) ) )<br /></code>
    <code class="source">        {<br /></code>
    <code class="source">            <span class="code-comment">/* Error management... */</span><br /></code>
    <code class="source">        }<br /></code>
    <code class="source">        <br /></code>
    <code class="source">        <span class="code-ctag">_sizeOfDelegatesArray</span> += <span class="code-num">10</span>;<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    hash = [ [ <span class="code-predefined">NSNumber</span> <span class="code-predefined">numberWithUnsignedInteger</span>: ( <span class="code-predefined">NSUInteger</span> )object ] <span class="code-predefined">stringValue</span> ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( [ <span class="code-ctag">_hashs</span> <span class="code-predefined">objectForKey</span>: hash ] != <span class="code-keyword">nil</span> )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">return</span>;<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-ctag">_delegates</span>[ <span class="code-ctag">_numberOfDelegates</span> ] = object;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    [ <span class="code-ctag">_hashs</span> <span class="code-predefined">setObject</span>: [ <span class="code-predefined">NSNumber</span> <span class="code-predefined">numberWithUnsignedInteger</span>: <span class="code-ctag">numberOfDelegates</span> ] <span class="code-predefined">forKey</span>: hash ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-ctag">_numberOfDelegates</span>++;<br /></code>
    <code class="source">}</code>
</div>
<p>
    We have previously allocated enough space for 10 delegates. If ten are set, and if another one is added, we just add space for 10 more objects with the 'realloc' function.
</p>
<p>
    Then we take the memory address of the object, as a string, and we check that the object is not already present in the delegates. This way, the same object can be added only once as a delegate.
</p>
<p>
    Finally, we need to store the pointer to our object, its memory address with its position in the pointer array, and incremenr by 1 the variable keeping the number of delegates.
</p>
<p>
    Now here's the method used to remove a delegate:
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )removeDelegate: ( <span class="code-keyword">id</span> )object<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSString</span>   * hash;<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span>   index;<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span>   i;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( object == <span class="code-keyword">nil</span> || <span class="code-ctag">_numberOfDelegates</span> == 0 )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">return</span>;<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    hash = [ [ <span class="code-predefined">NSNumber</span> <span class="code-predefined">numberWithUnsignedInteger</span>: ( <span class="code-predefined">NSUInteger</span> )object ] <span class="code-predefined">stringValue</span> ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( [ <span class="code-ctag">_hashs</span> <span class="code-predefined">objectForKey</span>: hash ] == <span class="code-keyword">nil</span> )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">return</span>;<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    index = [ [ <span class="code-ctag">_hashs</span> <span class="code-predefined">objectForKey</span>: hash ] <span class="code-predefined">unsignedIntegerValue</span> ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">for</span>( i = index; i < <span class="code-ctag">_numberOfDelegates</span> - <span class="code-num">1</span>; i++ )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-ctag">_delegates</span>[ i ] = <span class="code-ctag">_delegates</span>[ i + <span class="code-num">1</span> ];<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    [ <span class="code-ctag">_hashs</span> <span class="code-predefined">removeObjectForKey</span>: hash ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-ctag">_numberOfDelegates</span>--;<br /></code>
    <code class="source">}</code>
</div>
<p>
    It's the same kind of stuff, but with a little extra.
</p>
<p>
    Suppose we have 5 delegates, and that we removed the object placed at the third position of the array of pointers. We have a gap. To avoid this, we re-arrange all the pointers placed after the one we just removed.
</p>
<p>
    And finally, the method used to get an array containing all the delegate objects.
</p>
<div class="code">
    <code class="source">- ( <span class="code-predefined">NSArray</span> * )delegates<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span>       i;<br /></code>
    <code class="source">    <span class="code-predefined">NSMutableArray</span> * delegatesArray;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">if</span>( <span class="code-ctag">_numberOfDelegates</span> == <span class="code-num">0</span> )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">return</span> [ <span class="code-predefined">NSArray</span> <span class="code-predefined">array</span> ];<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    delegatesArray = [ <span class="code-predefined">NSMutableArray</span> <span class="code-predefined">arrayWithCapacity</span>: <span class="code-ctag">_numberOfDelegates</span> ];<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">for</span>( i = <span class="code-num">0</span>; i < <span class="code-ctag">_numberOfDelegates</span>; i++ )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        [ delegatesArray <span class="code-predefined">addObject</span>: <span class="code-ctag">_delegates</span>[ i ] ];<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">return</span> [ <span class="code-predefined">NSArray</span> <span class="code-predefined">arrayWithArray</span>: delegatesArray ];<br /></code>
    <code class="source">}<br /></code>
</div>
<p>
    It's just a loop on the array of pointers, that adds the pointed objects to a 'NSArray' object.
</p>
<a name="runtime"></a>
<h3>7. Runtime and method routing</h3>
<p>
    We've seen previously that we can use the 'respondsToSelector' method to check if a delegate has a specific method.
</p>
<div class="code">
    <code class="source"><span class="code-keyword">if</span>( [ <span class="code-ctag">_delegate</span> <span class="code-predefined">respondsToSelector</span>: <span class="code-keyword">@selector</span>( <span class="code-ctag">someMethod</span> ) ] )<br /></code>
    <code class="source">{}</code>
</div>
<p>
    We are going to implement that behaviour on the 'DelegateChain' class.
</p>
<p>
    Actually, the code we just see can't work, as the 'DelegateChain' object, which stores the delegates, does not implement their methods.
</p>
<p>
    But we can override (re-declare) in that class the 'respondToSelector' method (which is declared originally in the 'NSObject' class), so it has another behaviour than the default one.
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">BOOL</span> )<span class="code-predefined">respondsToSelector</span>: ( <span class="code-keyword">SEL</span> )selector<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span> i;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">for</span>( i = <span class="code-num">0</span>; i < <span class="code-ctag">_numberOfDelegates</span>; i++ )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">if</span>( [ <span class="code-ctag">_delegates</span>[ i ] <span class="code-predefined">respondsToSelector</span>: selector ] == <span class="code-keyword">YES</span> )<br /></code>
    <code class="source">        {<br /></code>
    <code class="source">            <span class="code-keyword">return</span> <span class="code-keyword">YES</span>;<br /></code>
    <code class="source">        }<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">return</span> <span class="code-keyword">NO</span>;<br /></code>
    <code class="source">}<br /></code>
</div>
<p>
    We are looping on the array of pointers, and we check if one of the delegates has the method. This way, we can use the 'DelegateChain' object as if it were a normal and unique delegate object.
</p>
<p>
    For this to work, we also have to override the 'methodSignatureForSelector' method (NSObject). It allows the Objective-C runtime environment to get informations about a specific method, like its return type, its arguments, etc.
</p>
<div class="code">
    <code class="source">- ( <span class="code-predefined">NSMethodSignature</span> * )methodSignatureForSelector: ( <span class="code-keyword">SEL</span> )selector<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span> i;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">for</span>( i = <span class="code-num">0</span>; i < <span class="code-ctag">_numberOfDelegates</span>; i++ )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">if</span>( [ <span class="code-ctag">_delegates</span>[ i ] <span class="code-predefined">respondsToSelector</span>: selector ] == <span class="code-keyword">YES</span> )<br /></code>
    <code class="source">        {<br /></code>
    <code class="source">            <span class="code-keyword">return</span> [ [ <span class="code-ctag">_delegates</span>[ i ] <span class="code-predefined">class</span> ] <span class="code-predefined">instanceMethodSignatureForSelector</span>: selector ];<br /></code>
    <code class="source">        }<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">return</span> <span class="code-keyword">nil</span>;<br /></code>
    <code class="source">}</code>
</div>
<p>
    Now we can know if at least one of the delegate objects has a specific method. But then how can we call it?
</p>
<p>
    We are going to keep the same way of calling a unique delegate. The method will be called directly on the 'DelegateChain' object, which will have to manage and re-route the call on the concerned delegates.
</p>
<p>
    We are going to implement the 'forwardInvocation' method:
</p>
<p>
    This method is automatically called by the Objective-C runtime environment when a method is called on an object which does not implement it. This way, the object has a last chance to manage the error.
</p>
<p>
    The same kind of concept is used in many different programming languages. It can be seen like C++ virtual function, or like the PHP5 '__call' method.
</p>
<div class="code">
    <code class="source">- ( <span class="code-keyword">void</span> )forwardInvocation: ( <span class="code-predefined">NSInvocation</span> * )invocation<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSUInteger</span> i;<br /></code>
    <code class="source">    <br /></code>
    <code class="source">    <span class="code-keyword">for</span>( i = <span class="code-num">0</span>; i < <span class="code-ctag">_numberOfDelegates</span>; i++ )<br /></code>
    <code class="source">    {<br /></code>
    <code class="source">        <span class="code-keyword">if</span>( [ <span class="code-ctag">_delegates</span>[ i ] <span class="code-predefined">respondsToSelector</span>: [ invocation <span class="code-predefined">selector</span> ] ] == <span class="code-keyword">YES</span> )<br /></code>
    <code class="source">        {<br /></code>
    <code class="source">            [ invocation <span class="code-predefined">invokeWithTarget</span>: <span class="code-ctag">_delegates</span>[ i ] ];<br /></code>
    <code class="source">        }<br /></code>
    <code class="source">    }<br /></code>
    <code class="source">}</code>
</div>
<p>
    The delegate chain system is now functionnal. To use it in a class, we just have to extend the 'MultipleDelegateObject' class. Nothing more is needed.
</p>
<a name="conclusion"></a>
<h3>8. Afterwords</h3>
<p>
    Such a system allows to define classes with an infinite number of delegates. But of course the Cocoa core objects, like 'NSWindow', won't be able to use that system.
</p>
<p>
    That said, it is possible to implement that multiple delegate system on objects like 'NSWindow'.
</p>
<p>
    The Objective-C language allows the definition of categories, which allows methods to be added on any existing class, even if it's a core Objective-C class. For instance:
</p>
<div class="code">
    <code class="source"><span class="code-keyword">@interface</span> <span class="code-predefined">NSObject</span>( MyCategory )<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )sayHello;<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span><br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@implementation</span> <span class="code-predefined">NSObject</span>( MyCategory )<br /></code>
    <code class="source"><br /></code>
    <code class="source">- ( <span class="code-keyword">void</span> )sayHello<br /></code>
    <code class="source">{<br /></code>
    <code class="source">    <span class="code-predefined">NSLog</span>( <span class="code-string">@"hello, world"</span> );<br /></code>
    <code class="source">}<br /></code>
    <code class="source"><br /></code>
    <code class="source"><span class="code-keyword">@end</span></code>
</div>
<p>
    This code adds a 'sayHello' method in the 'NSObject' class, which is part of the Cocoa framework. As 'NSObject' is the root class of all Objective-C classes, all available classes will respond to the 'sayHello' method.
</p>
<p>
    So we could add in the same way the 'addDelegate', 'removeDelegate' and 'delegates' methods to the 'NSWindow' object.
</p>
<p>
    The only limitation with categories is that we cannot add instance variables to a class. But the 'NSWindow' object already have an instance variable for the delegate. We'll just have to override the 'setDelegate' method of 'NSWindow' in the category. A global static variable (whose access is limited to the file which declared it) is also a possibility to store the delegate chains.
</p>
