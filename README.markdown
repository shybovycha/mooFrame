#  mooFrame

_mooFrame_ is a PHP framework, supposed to be a very lightweight and simple. 

It is developed in a **very specific** way to make the use of itself most comfortable and easy for **me** (not you).

## Getting Started

*  First of all you need to set up some web-server with PHP support.
*  Then, create a virtual host with `httpdocs` (or something like that) directory pointing to `/www/` directory of your framework copy
*  Create your application within `app/` directory
  *  Create directory named `YourApplicationName` within `app/` 
  *  Create `YourApplicationName/etc/` directory with just one file - `routes.php` within it
  *  Add line `$isDefault = true;` to the `routes.php` file
  *  Create `YourApplicationName/controller/` directory with `index.php` file within
  *  Add some code to `index.php` file. For example, you could _echo_ some text
*  Go to your virtual host URL and proceed with development!

## Views

*  Create `YourApplicationName/view/` directory with `MyTemplate.phtml` file within
*  Add this code to it:

	&lt;head&gt;
		&lt;title&gt;
			&lt;?php echo $this-&gt;title; ?&gt;
		&lt;/title&gt;
	&lt;/head&gt;
	&lt;body&gt;
		&lt;h1&gt;&lt;?php echo $this-&gt;body; ?&gt;&lt;/h1&gt;
	&lt;/body&gt;
  
*  Change the `YourApplicationName/controller/index.php` file's code to

    Renderer::render('view/MyTemplate.phtml', array('title' => 'My First Application', 'body' => 'Hello, World!'));
    
*  Go to your host in web-browser and enjoy =)
