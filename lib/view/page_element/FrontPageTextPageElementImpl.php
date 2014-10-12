<?php
namespace ChristianBudde\cbweb\view\page_element;
use ChristianBudde\cbweb\BackendSingletonContainer;
use ChristianBudde\cbweb\model\page\PageContentImpl;


/**
 * Created by JetBrains PhpStorm.
 * User: budde
 * Date: 25/05/13
 * Time: 21:55
 * To change this template use File | Settings | File Templates.
 */

class FrontPageTextPageElementImpl extends PageElementImpl
{

    private $container;

    public function __construct(BackendSingletonContainer $container)
    {

        $this->container = $container;
    }

    /**
     * This will return content from page element as a string.
     * The format can be xml, xhtml, html etc. but return type must be string
     * @return string
     */
    public function generateContent()
    {
        parent::generateContent();
        $latest= null;
        $pageContent = new PageContentImpl($this->container->getDBInstance(), $this->container->getCurrentPageStrategyInstance()->getCurrentPage(), "mainContent");
        if(($latest = $pageContent->latestContent()) != null){
            return "<div class='editable' id='mainContent'>$latest</div>";
        }
        return "
        <div class='editable' id='mainContent'>
<h2>Lorem Ipsum</h2>
<p>
Lorem ipsum dolor sit amet, consectetur adipiscing elit. In at lobortis nibh. Nullam lobortis nunc sed iaculis fermentum. Donec convallis sapien non nunc rhoncus rhoncus. Nulla et lorem sed nibh varius dignissim. Nullam quis libero a risus volutpat feugiat in eget sem. Sed quis vehicula ipsum, et laoreet tellus. Suspendisse nec lectus sit amet massa tristique ultrices. Maecenas vitae est tortor. Mauris quis mollis arcu. Nam blandit dictum erat dignissim pulvinar. Cras nec vehicula risus. Etiam viverra quam orci, at convallis tortor tincidunt a. Duis mollis, diam sed vestibulum tempus, justo augue venenatis enim, eu sodales ipsum est sollicitudin ligula. Proin vel laoreet enim.
</p>
<pre>initLib.registerInitializer(new UserSettings.UserSettingsInitializer(initLib));
initLib.registerInitializer(new BackgroundPositionInitializer());
initLib.registerInitializer(new TopMenuInitializer());
initLib.registerInitializer(new LoginFormulaInitializer());
initLib.registerInitializer(new EditorInitializer());
initLib.setUp();</pre>
<p>
Nunc varius tellus tellus, sed semper ligula hendrerit id. Fusce placerat lectus posuere quam imperdiet accumsan. Suspendisse non lacinia tellus. Etiam lacinia ullamcorper consectetur. Mauris dapibus ligula ac odio porttitor tempor. Mauris accumsan hendrerit pellentesque. Praesent suscipit lectus sit amet adipiscing aliquam. Praesent venenatis cursus lorem ac pretium. Proin ultricies libero nec neque aliquam fringilla. Etiam sed condimentum eros, id vulputate dui. Nunc consectetur volutpat risus. Cras venenatis libero eget viverra pulvinar.
</p>
<h3>Phasellus aliquam </h3>
<p>
Phasellus aliquam orci ut scelerisque fringilla. Donec accumsan erat vehicula lectus ultrices lacinia. Vivamus eu orci feugiat tellus porttitor mollis ac eu nisi. Curabitur quis turpis id tortor scelerisque placerat posuere et lectus. Proin cursus mi eget dui pretium, quis imperdiet arcu ultricies. Donec at nibh enim. Maecenas suscipit euismod turpis ut venenatis. Sed tincidunt lacus urna, non sodales mi dapibus a. Nulla nec enim eu magna accumsan dapibus. Nulla eu tincidunt orci, eget ornare ante.
</p>
<blockquote>
Nunc varius tellus tellus, sed semper ligula hendrerit id. Fusce placerat lectus posuere quam imperdiet accumsan. Suspendisse non lacinia tellus. Etiam lacinia ullamcorper consectetur. Mauris dapibus ligula ac odio porttitor tempor. Mauris accumsan hendrerit pellentesque. Praesent suscipit lectus sit amet adipiscing aliquam. Praesent venenatis cursus lorem ac pretium. Proin ultricies libero nec neque aliquam fringilla. Etiam sed condimentum eros, id vulputate dui. Nunc consectetur volutpat risus. Cras venenatis libero eget viverra pulvinar.
</blockquote>




        </div>
        ";
    }

}