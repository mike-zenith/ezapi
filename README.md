ezapi
=====

```php

$api->query( $api->user->post(array('id' => 12, 'name' => 'ezapi tester')) );
$api->query( $api->user(12)->badge->push(array('name' => 'best singer')) );
$api->query( $api->user(12)->badge->put(1, array('name' => 'best singer ever')) );
$api->query( $api->user(12)->badge->delete(1) );
```

