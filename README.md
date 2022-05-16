<!-- statamic:hide -->

![Statamic](https://flat.badgen.net/badge/Statamic/3.3+/FF269E)
![Packagist version](https://flat.badgen.net/packagist/v/jacksleight/statamic-bonus-routes)
![License](https://flat.badgen.net/github/license/jacksleight/statamic-bonus-routes)

# Bonus Routes 

<!-- /statamic:hide -->

> **⚠️ Experimental:** This addon is experimental and could change. If you’re testing this out and have any feedback, suggestions or issues please [get in touch](https://github.com/jacksleight/statamic-bonus-routes/issues).

This Statamic addon simplifies setting up additional collection and taxonomy based routes by handling the dynamic mounting and data retrieval for you. This is useful for things like:

* Adding registration pages below entry pages in an events collection
* Adding date based archive pages above entry pages in a blog collection
* Mounting a filtered news collection to different sections of a site
* Customising and mounting taxonomy urls

All of this is possible by writing your own custom routes and controllers, this addon just makes it simpler and saves you hard coding the URLs.

## Installation

You can search for this addon in the `Tools > Addons` section of the Statamic control panel and click **install**, or run the following command from your project root:

```bash
composer require jacksleight/statamic-bonus-routes
```

## Getting Started

You can define bonus routes using the `Route::bonus()` method. These should be added to your `routes/web.php` file. This method accepts the following arguments:

* **type (string):** The type of route and handle
* **uri (string):** The route URI
* **view (string):** The name of the view that should be rendered
* **data (array, optional):** Any addiitonal data to parse to the template

### Collection Routes

Two types of collection route are supported, show and index. Show routes work in exactly the same way as Statamic's standard routes, they parse the requested entry to the view template or 404 if nothing is found. Index routes are for listing and general non-entry specific pages.

Collection show routes *must* include all parameters that Statamic's standard route uses. They can included additonal parameters, and they can be in a different order, but they must all be there.

These are some example bonus collection routes. *Use braces not brackets*, I had to change them here due to formatting issues:

```php
// Add a date based blog archive page
Route::bonus('collection:blog', '(mount)/(year)', 'blog.archive');

// Add a seperate blog post comments page
Route::bonus('collection:blog', '(mount)/(year)/(slug)/comments', 'blog.comments');

// Mount a blog collection to an additional entry
Route::bonus('collection:blog', '(mount:entry-id)/(year)/(slug)', 'blog.show');
```

### Taxonomy Routes

Two types of taxonomy route are supported, show and index. Show routes work in exactly the same way as Statamic's standard routes, they parse the requested term to the view template or 404 if nothing is found. Index routes are for listing and general non-term specific pages.

Taxonomy show routes *must* include a `slug` parameter.

These are some example bonus taxonomy routes. *Use braces not brackets*, I had to change them here due to formatting issues:

```php
// Customise a taxonomy's URL
Route::bonus('taxonomy:topics', 'categories/(slug)', 'topics.show');

// Add a seperate posts page under a taxonomy term
Route::bonus('taxonomy:topics', 'topics/(slug)/posts', 'topics.posts');

// Mount a taxonomy to an entry
Route::bonus('taxonomy:topics', '(mount:entry-id)/(slug)', 'topics.show');
```

### Linking to Routes

Bonus routes are just normal Laravel routes. To link to them you need to give them a name and then use the `route` tag in your templates. To give them names call Laravel's name method after defining your route:

```php
Route::bonus('collection:blog', '(mount)/(year)', 'blog.archive')->name('blog.archive');
```

Then use the `route` tag in your templates:

```html
{{ route:blog.archive year="2022" }}
```

## Route Caching

Bonus routes are just normal Laravel routes, which means they’ll be cached when using route caching. This means that changes to your mount entries will not be reflected in your routes automatically. This addon provides a utility that allows you to refresh the route cache through the control panel, which you can find under `Utilities > Route Cache Refresh`. Alternatively you can refresh the cache on the command line by running `php artisan route:cache`.

## Route Overriding

This addon itself does not override, alter or interfere with Statamic’s routing in any way. However, custom Laravel routes do take priority over Statamic routes. If you define a bonus route that’s the same as a Statamic route it will override Statamic. This should be avoided, it’s best to use Statamic’s routing wherever possible.

```php
Test: {test}
```
