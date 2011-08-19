#  mooFrame [0.1.0]

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
