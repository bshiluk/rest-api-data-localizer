# WP REST API Data Localizer

> Localize a store of normalized WP REST API response data. Useful for sharing data and avoiding initial API requests when using Wordpress and the WP REST API in combination with front-end frameworks like Vue, React, and Angular.

## Getting Started

1. Upload this repo to your `/wp-content/plugins/` directory
2. Activate as you would any other Wordpress plugin

## Usage

### Initialize your store

````php
new RADL( 'my_store', 'script_handle', array());
````

- `'my_store'` is the name of your localized store
- `'script_handle'` is the handle of the script your store will be localized to
- `'array()'` defines the schema for your store

### Initialize your store with an endpoint

````php
new RADL( 'my_store', 'script_handle', array(
  'my_posts' => RADL::endpoint( 'posts' )
));
````
- `'my_posts'` is the key used to call the endpoint
- `RADL::endpoint( 'posts' )` initializes an endpoint corresponding to `'/wp/v2/posts'`

#### Now you can make requests to `'/wp/v2/posts'` internally.

````php
// request the 10 most recent posts
RADL::get('my_posts', array( 'per_page' => 10 ));

// request a post with an id of 12
RADL::get('my_posts', 12);

````
  - If the second argument is an array, it is used as the arguments for the request, otherwise it is treated as the unique identifier for a resource at the specified endpoint
    - The first request is equivalent to `GET /wp/v2/posts/?per_page=10`
    - The second request is equivalent to `GET /wp/v2/posts/12`

#### Access WP REST API Data in Templates

````php
/**
 * Single.php
 */

$my_post = RADL::get('my_posts', get_the_ID());

echo $my_post['title']['rendered'];
// outputs title of post
````

### Preload endpoint in store with response data

````php
new RADL( 'my_store', 'script_handle', array(
  'my_posts' => RADL::endpoint( 'posts', array( 12, array( 'per_page' => 10 ) ) )
));
````
- The second argument of `RADL::endpoint()` is an array of request arguments
- Is the equivalent of calling `RADL::get()` for each value
  - `12` adds response data from `GET /wp/v2/posts/12`
  - `array( 'per_page' => 10 )` adds response data from `GET /wp/v2/posts/?per-page=10`

### Initialize your store with a callback

````php
new RADL( 'my_store', 'script_handle', array(
  'site' => RADL::callback( 'site_info' )
));

function site_info( $arg = 'default' ) {
  return array(
    'name' => get_bloginfo('name'),
    'desc' => get_bloginfo('desc'),
    'url'  => get_bloginfo('wp_url'),
    'arg'  => $arg
  );
}
````
- `'site'` is the key used to access data returned from the callback
- `RADL::callback('site_info')` initializes a callback where `'site_info'` is a callable that will be called by `call_user_func_array()`

#### Call and access returned data

````php
echo RADL::get( 'site' )['name'];
// outputs equivalent of get_bloginfo('name')

echo RADL::get( 'site', array( 'too late' ) )['arg'];
// outputs 'default'
// would output 'too late' if not previously called without arguments
````
- Callbacks are only called once 
- If not called explicitly, will be called by default with no arguments before being localized

### Initializing Store With Nested Structure
````php
new RADL( 'my_store', 'script_handle', array(
  'state' => array(
    'posts' => RADL::endpoint( 'posts' ),
    'site'  => RADL::callback( 'site_info' )
));
````
- Use dot syntax for nested keys when using `RADL::get()`
  - `RADL::get( 'state.posts', array( 'per_page' => 10 ) )`
  - `RADL::get( 'state.site', array( 'some_val' ) )`

## Complete Example
*As used in [Vue.wordpress](https://github.com/bucky355/vue-wordpress/)*
````php
new RADL( '__VUE_WORDPRESS__', 'vue_wordpress.js', array(
    'routing' => RADL::callback( 'vue_wordpress_routing' ),
    'state' => array(
        'categories' => RADL::endpoint( 'categories'),
        'media' => RADL::endpoint( 'media', array( 7 ) ),
        'menus' => RADL::callback( 'vue_wordpress_menus' ),
        'pages' => RADL::endpoint( 'pages' ),
        'posts' => RADL::endpoint( 'posts', array( array( 'per_page' => 6 ) ) ),
        'tags'  => RADL::endpoint( 'tags' ),
        'users' => RADL::endpoint( 'users' ),
        'site'  => RADL::callback( 'vue_wordpress_site' ),
    )
) );
````
To view example output:
1. Go to [vue-wordpress.com](http://vue-wordpress.com)
2. Open Developer Tools
3. Type `__VUE_WORDPRESS__` in the console and press enter
