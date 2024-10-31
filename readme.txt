=== Portico ===
Contributors: danbettles
Donate link: http://danbettles.net/
Tags: custom post type, custom post types, content type, content types, custom type, custom types, cms, wordpress 3, php 5.3, phpunit, custom posttype, custom posttypes, contenttype, contenttypes
Requires at least: 3.0
Tested up to: 3.5.1
Stable tag: 1.0.2

Portico makes defining and administering custom post-types EASY.  Define your post-type in a class, and an admin
interface will be created for you.

== Description ==

= Overview =

There are just two steps to implementing a custom post-type with Portico.  Here's a quick overview before we get down
to the coding required.

* Declare a class that defines your custom post type.
* Create a display template for the new post type.  

That's it!  Portico handles the rest for you, and that includes building an admin interface for working with posts of
the new type.

= Coding =

Let's take a look at a simple example, the implementation of a Podcast post-type.

Here's what the custom post-type's definition looks like:

`<?php

/**
 * Podcast custom post-type implemented using the Portico plugin
 */
class Podcast extends \portico\CustomPostType
{
    protected function setUp()
    {
        //At present, "mandatory" simply marks the field as mandatory in the admin interface
        $this->addCustomField('artist', 'Artist', array(
            'mandatory' => true,
        ));

        //Default values can also be applied to text fields
        $this->addCustomField('genre', 'Genre', array(
            'values' => array(
                'Ambient' => 'Ambient',
                "Drum 'n' Bass" => "Drum 'n' Bass",
                'Dubstep' => 'Dubstep',
            ),
            'default' => "Drum 'n' Bass",
        ));

        //Set "length" to NULL to get a textarea
        $this->addCustomField('trackList', 'Track List', array(
            'length' => null,
        ));
    }
}
?>`

Put the code in `functions.php` - or in a separate file *include*d by `functions.php` - in your theme.

The next time you visit the WordPress admin area you should see a "Podcasts" section in the left-hand menu, beneath the
usual "Posts" and "Pages" links.  Take a look at the first screenshot to see exactly what you can expect.

All that remains is to create a custom display-template so we can view the values of a podcast's custom fields.

You can start by making a copy of `single.php` and renaming it after your custom post-type.  Here's the template for the
Podcast type, based on `single.php` shipped with the Modern Clix 1 theme:

`<?php get_header() ?>

<div id="content" class="col span-8">

<?php if (have_posts()) : ?>

    <div class="col last span-6 nudge-2">
		<h4 class="ver small">You are reading</h4>	
	</div>
	
    <?php while (have_posts()) : the_post() ?>
        <?php $podcast = new Podcast($post, get_post_custom(get_the_ID())) ?>

    <div class="post">
        <div class="post-meta col span-2">
            <ul class="nav">
                <li>Artist: <?php echo $podcast->getSingleCustomFieldValue('artist') ?></li>
                <li>Genre: <?php echo $podcast->getSingleCustomFieldValue('genre') ?></li>
            </ul>
        </div>
        
        <div class="post-content span-8 nudge-2">
            <h3><a href="<?php the_permalink() ?>" rel="bookmark" title="Permanent Link to <?php the_title_attribute() ?>"><?php the_title() ?></a></h3>

            <?php the_content('Continue reading...') ?>

            <p><?php echo nl2br($podcast->getSingleCustomFieldValue('trackList')) ?></p>
        </div>
    </div>
    
    <?php comments_template() ?>

    <?php endwhile ?>

<?php else : ?>

    <h3>Post Not Found</h3>

    <p>Sorry, but you are looking for something that isn't here.</p>

<?php endif ?>
	
</div>

<hr />

<?php get_sidebar() ?>
<?php get_footer() ?>`

Important to note here is the line that creates an instance of your custom post type, `<?php $podcast = new Podcast($post, get_post_custom(get_the_ID())) ?>`.
It's from this instance that we get the values of the custom fields.

Take a look at the second screenshot to see what you can expect from this template.

== Installation ==

**N.B. Portico requires PHP 5.3.**

1. Install the plugin under the `/wp-content/plugins/` directory.
1. Activate the plugin through the "Plugins" menu in WordPress.

Follow the instructions on the Description tab to get started creating custom post-types with Portico.

== Upgrade Notice ==

N/A

== Frequently Asked Questions ==

= My WordPress site is running on a version of PHP older than PHP 5.3.  Why does Portico not work? =

Portico requires PHP 5.3.

= I receive a 404 when trying to view a Portico custom post type.  What gives? =

Try logging-in to, and then logging-out of, the admin area.  This should force WordPress to regenerate its URL rewriting
rules, which could be the source of the problem.  

== Screenshots ==

1. The admin interface created by Portico for the "podcast" custom post-type described in the example.  Podcasts are
accessed via a link in the left-hand menu; permalinks contain the slug "podcast"; the custom type's fields appear in a
section underneath the main content editor.
1. Output from the display template for the "podcast" custom post-type described in the example.  

== Changelog ==

N/A