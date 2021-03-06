WordPress reCaptcha Integration
===============================

This is the official github repository of the [WP reCaptcha integration plugin](https://wordpress.org/plugins/wp-recaptcha-integration/) 
plugin. This repo might contain untested and possibly unstable or insecure code. So use it on your own risk. 

Features
--------
- Secures login, signup and comments with a recaptcha.
- Supports old as well as new reCaptcha.
- [Ninja Forms](http://ninjaforms.com/) integration
- [Contact Form 7](https://wordpress.org/plugins/contact-form-7/) integration
- Tested with up to WP 4.2-alpha, Ninja Forms 2.8.7, Contact Form 7 4.1

Limitations
-----------
- You can't have more than one old style reCaptcha on a page. This is a limitiation of 
  reCaptcha itself. If that's an issue for you, you should use the no Captcha Form.

- On a Contact Form 7 when the reCaptcha is disabled (e.g. for logged in users) the field
  label will be still visible. This is due to CF7 Shortcode architecture, and can't be fixed.

  To handle this there is a filter `recaptcha_disabled_html`. You can return a message for your logged-in 
  users here.

Plugin API
----------

#### Action `wp_recaptcha_checked`

Fires after a recaptcha has been checked.

##### Example

```
// will disable recaptcha for nice spambots
function my_recaptcha_required( $is_required ) {
	if ( is_nice_spambot() )
		return false;
	else if ( is_ugly_spambot() )
		return true;
	else
		return $is_required;
}
add_filter('wp_recaptcha_required','my_recaptcha_required');
```

##### Real World Example

Disable captcha if it has been solved once.
```
// safely start a session
function my_session_start( ) {
	$sid = session_id();
	if ( empty( $sid ) ) {
		session_start();
	}
}
add_action('init','my_session_start');

// don't requiere captcha, if session says so
function my_wp_recaptcha_required( $is_required ) {
	if ( isset( $_SESSION['recaptcha_solved'] ) && $_SESSION['recaptcha_solved'] )
		return false;
	return $is_required;
}
add_filter('wp_recaptcha_required' , 'my_wp_recaptcha_required');

// store in session if captcha solved
function my_wp_recaptcha_checked( $success ) {
	vaR_dump($success);
	if ( $success )
		$_SESSION['recaptcha_solved'] = true;
}
add_action('wp_recaptcha_checked','my_wp_recaptcha_checked');
```


#### Filter `wp_recaptcha_required`

Returns whether to show a recaptcha or not.

##### Example
```
// will disable recaptcha for nice spambots
function my_recaptcha_required( $is_required ) {
	if ( is_nice_spambot() )
		return false;
	else if ( is_ugly_spambot() )
		return true;
	else
		return $is_required;
}
add_filter('wp_recaptcha_required','my_recaptcha_required');
```


#### Filter `wp_recaptcha_disabled_html`

HTML to be showed when entering a recaptcha is not required.

##### Example
```
// will disable recaptcha for nice spambots
function my_recaptcha_disabled_html( $html ) {
	return 'Not four you, my friend!';
}
add_filter('wp_recaptcha_disabled_html','my_recaptcha_disabled_html');
```

#### Filter `wp_recaptcha_language`

Support
-------
You like what you see? Maybe you already make some money with it? 
Here are two ways to keep me rocking:

[![Flattr this git repo](http://api.flattr.com/button/flattr-badge-large.png)](https://flattr.com/submit/auto?user_id=joern.lund&url=https://github.com/mcguffin/wp-recaptcha-integration&title=WP%20Recaptcha%20Integration&language=php&tags=github&category=software)
<a href="https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=F8NKC6TCASUXE"><img src="https://www.paypalobjects.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!" /></a>
