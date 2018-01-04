##### Change your routes without changing your templates

##### Example
If you have a route like this:

```php
/**
 * @Route("/{id}", name="post_detailed")
 */
public function indexAction(Post $post)
{...}
```

then your templates will have this:

```html
<a href="path('post_detailed', {'id': new_post.id})">View post</a>
```

*For this simple case, [ParamConverter](https://symfony.com/doc/current/best_practices/controllers.html#using-the-paramconverter) is automatically applied.*

##### The problem
Imagine you want to change it to use slug instead of id in your URL. Updating controller is simple, but updating all the templates is not so.

##### The solution

This bundle acts as ReverseParamConverter. Check this example:

```php
/**
 * @Route("/{id}", name="post_detailed")
 * @RewireParams(requires={"post"}, rewire={"id"="post.id"})
 */
public function indexAction(Post $post)
{...}
```

and the template:

```html
<a href="path('post_detailed', {'post': new_post})">View post</a>
```

Now instead of ``new_post.id`` you can use object itself and this bundle will use it to generate {id} needed for route.

So if you want to slug instead of id in your route, just do this:

```php
/**
 * @Route("/{slug}", name="post_detailed")
 * @RewireParams(requires={"post"}, rewire={"slug"="post.slug"})
 */
public function indexAction(Post $post)
{...}
```

and that's all.

#### Note

If you install this bundle, you don't have to change all your templates. So both this combinations will work:


```html
<a href="path('post_detailed', {'id': new_post.id})">View post</a>

<a href="path('post_detailed', {'post': new_post})">View post</a>
```

#### Complex routes
The above example is pretty simple so let's try something more complex. For this, imagine we wanted to add ``category_slug`` to URL for better SEO.

The old way:

```php
/**
 * @Route("/{category_slug}/{slug}", name="post_detailed")
 */
public function indexAction(Post $post)
{...}
```

```html
<a href="path('post_detailed', {'category_slug': new_post.category.slug, 'slug': post.slug})">View post</a>
```

For this fictional blog application, generated route would be something like ``/conspiracy-theories/why-they-suck``. You can see why updating templates can be a problem.

With this bundle:


```php
/**
 * @Route("/{category_slug}/{slug}", name="post_detailed")
 * @RewireParams(requires={"post"}, rewire={"category_slug"="post.category.slug", "slug": post.slug})
 */
public function indexAction(Post $post)
{...}
```

and the template is still the same as if you used ``{id}`` only.

```html
<a href="path('post_detailed', {'post': post})">View post</a>
```



#### Installation
    composer require wjb/rewire-bundle


If you are on Symfony4, you don't have to do anything else. For Symfony3, enable the bundle in your AppKernel:

    class AppKernel extends Kernel
    {
        public function registerBundles()
        {
            $bundles = [
                ...
                new wjb\RewireBundle\wjbRewireBundle(),
                ...
            