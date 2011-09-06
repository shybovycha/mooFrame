#  mooFrame [0.1.0]

![logo](http://github.com/shybovycha/mooFrame/raw/alpha/logo.png)

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

		<head>
			<title>
				<?php echo $this->title; ?>
			</title>
		</head>
		<body>
			<h1><?php echo $this->body; ?></h1>
		</body>
 
*  Change the `YourApplicationName/controller/index.php` file's code to

		Renderer::render('view/MyTemplate.phtml', array('title' => 'My First Application', 'body' => 'Hello, World!'));
    
*  Go to your host in web-browser and enjoy =)

## Database

* Create a database. For this example I'll use MySQL database named 'test' with just one table 'logs': 

		CREATE DATABASE test;
		USE test;
		CREATE TABLE logs (id INT PRIMARY KEY AUTO_INCREMENT, message TEXT NOT NULL);
		INSERT INTO logs (message) VALUES ('The time has come for us'), ('To say our last goodbye`s'), ('And now it's finally the time'), ('To leave it all behind'), ('We Own the night'), ('As daylight dies'), ('We Own the night!');

* _Thanks for lyrics to [DarkLyrics and Made Of Hate](http://www.darklyrics.com/lyrics/madeofhate/pathogen.html#5)_

* Make these little changes to your `index` controller' code:

		Config::set('dbLogQuery', TRUE);
		$conn = Database::connect('mysql:host=YOUR DATABASE HOST;dbname=test', 'YOUR DATABASE USER', 'YOUR DATABASE PASSWORD');
		$res = Database::query($conn, "select * from logs where message like :ow limit 10;", array(':ow' => '%ow%'));
		Renderer::render('view/template.phtml', array('moo' => 'moo title', '_content' => '<h1>Hello, World!</h1>', 'rows' => $res));

* Do not forget to replace `YOUR DATABASE HOST`, `YOUR DATABASE USER` and `YOUR DATABASE PASSWORD` options with the right values!

* Create `app/YourApplication/view/dbgrid.phtml` file with these lines added:

		<table>
			<?php $first = TRUE; ?>
			<?php foreach ($this->rows as $r): ?>
				<tr>
					<?php foreach ($r as $k => $c): ?>
						<?php if (!$first): ?>
							<td><?php echo $c; ?></td>
						<?php else: ?>
							<td><?php echo $k; ?></td>
						<?php endif; ?>
					<?php endforeach; ?>
				</tr>
				<?php if ($first) $first = FALSE; ?>
			<?php endforeach; ?>
		</table>

* And add rendering li__m__e to your `app/YourApplication/view/template.phtml` file:

		<?php Renderer::partial('view/dbgrid.phtml', array('rows' => $this->rows)) ?>

* Go to your web-browser and verify the table with a few lyrics lines =)

## Extensions

* Create extension folder `ext/YourExtension/` with the files you will need. Let's create just one, `MyHelper.php`

* Make your application depend on your new extension file: create file `app/YourApplication/etc/config.php` with this code:

		<?php
			$depends = array(
				'MyExtension:MyHelper'
			);
		?>

* Add some helper functions to `ext/YourExtension/MyHelper.php`:

		<?php
			function quote($text)
			{
				return "<p><i>$text</i></p><br />";
			}
		?>

* Invoke your extension, for example, in `app/YourApplication/view/template.phtml`:

		<?php echo Router::ext('MyExtension/MyHelper.php:quote', 'Lorem ipsum... Blah-blah-blah... That makes no sense!'); ?>
		
* If you wanna use classes, you should name the file and the class within that file with the same name. Let's create `Mooer.php` file within `ext/MyExtension/`:

		<?php
			class Mooer
			{
				static function moo()
				{
					$args = func_get_args();
					
					$r = '';
					
					foreach ($args as $v)
					{
						$r .= "<i>Moo~, $v, moo~!</i><br />';
					}
					
					return $r;
				}
			}
		?>

 	When you make a call to such extension, you should pass just function name: 
 			
 			Router::ext('MyExtension/Mooer.php:moo', 'Joe', 'Daniel', 'Mary')
 			
 	Again, __you should not use complete function name like *class::function* when invoking an extension__!
 	Sure, you _may_, but you __should not__.
 	
## Events

If you need to communicate between different parts of your application (for example, controller and extension routines), you may use a `Dispatcher` class.
To pass execution to some function chain, you need two things:

- Register some function set to handle certain event: 
	
		Dispatcher::subscribe('MooEventSignal', 'app:MooApplication/controller/index.php:someFunction');
	
	Currently, application and extension handlers are supported only. Handler string format is (PCRE used): 
		
		(app|ext):(file path relative to app/ or ext/ dir)(:function name)?

	If you have not passed function name, the whole file would be executed (like controllers, don't you remember? 'use classes only if you need them. if not - use raw PHP files!').
                                                                                                                                                                                                                                                                                                                             - 
- Fire that event: `Dispatcher::fire('MooEventSignal');`. All handlers will be invoked in the same order as they were subscribed.
#
