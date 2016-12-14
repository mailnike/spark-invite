# Spark Invite

## Installation
- Edit `config\app.php` to have:
```php
    ...
    'providers' => [
        ...
        ZiNETHQ\SparkInvite\SparkInviteServiceProvider::class,
        ...
    ],
    ...
    'aliases' => [
        ...
        'SparkInvite' => ZiNETHQ\SparkInvite\Facades\SparkInvite::class,
        ...
    ],
    ...
```
- Run the command:
```php
php artisan vendor:publish --provider="ZiNETHQ\SparkInvite\SparkInviteServiceProvider"
```
-
- Add the following to your `App\Providers\EventServiceProvider` class:
```php
    protected $listen = [
        ...
        'spark.invite.*' => [
            'App\Listeners\InvitationListener',
        ],
        ...
    ];
```

## Managing errors
When there is an issue with a token, e.g. doesn't exist or has been revoked, the invitation controller redirects back to the route defined in the configuration setting `sparkinvites.routes.on-error`. When redirecting the user one of the `sparkinvites.messages` is passed under the variable name configured by `sparkinvite.flash`, which can be displayed in your view. For example, using Vue.js and Notify.js (plus underscore.string/lodash for capitalization and sprintf for string creation) the following component will do this:
```js
Vue.component('alert', {

    props: {
        message: {
            type: Object,
            required: false,
            default: null
        }
    },

    computed: {
        title: function () {
            if (!this.message) {
                return;
            }

            return sprintf('<strong>%s</strong>', _string.capitalize(this.message.type));
        },
        type: function () {
            switch (this.message.type) {
                case 'error':
                    return 'danger';
                case 'warning':
                    return 'warning';
                case 'success':
                    return 'success';
                case 'info':
                    return 'info';
                default:
                    return 'danger';
            }
        }
    },

    template: '',

    created: function () {
        this.$nextTick(function () {
            if (!this.message) {
                return;
            }

            $.notify({
                icon: 'font-icon fa fa-times-circle',
                title: this.title,
                message: this.message.content
            },{
                type: this.message.type
            });
        });
    }

});

```
When used like so on your view page:
```HTML
<alert :message="{{ json_encode(session()->get('alert'), null) }}"></alert>
```