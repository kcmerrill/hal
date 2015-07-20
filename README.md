# hal
Realtime web goodness.

![HAL - Realtime web goodness.](https://raw.githubusercontent.com/kcmerrill/hal/master/html/images/HAL-www.png)

### Quick Summary
---
Hal is a simple pub/sub event system that aims to abide by K.I.S.S(Keep it simple stupid). There are 2 main components to HAL. The client and the server. 
Between these two components, you have 3 simple items to keep track of. All of which will be described in more detail below:

### Message
A message is a json string that requires only two keys. "to" and "signature". The to key can be the channel_name, or it can be channel_name.event. You can add as many additional keys as you want, but the return message will contain a few additional keys: created_date, sent_date, from. Where the dates contain epoch timestamps, and from contains an array of the subscriber metadata. Keeping in line with K.I.SS, you can send along any paramaters you wish. What you do with those parameters on the client side are up to you!
```json
{"to": "hal-demo", "msg":"Good Morning Dave", "signature":"dave"}
```

Another great feature is the ability to curl in your messages via http, to your websocket server. 

```bash
# Notice, the only two required keys are to and signature. Where signature is the _id of the subscriber. 
# Any other keys that are submitted are completely up to you. 
# Hal only cares about delievering the message to those who are authetnicated to the correct channels.

curl -d '{"to": "hal-demo", "msg":"Good Morning Dave", "signature":"dave"}' hal.kcmerrill.com
```

#### Channel
A channel is simply a string that represents a place to submit messages to. 
```
/* Example creating a channel via php */
$channel = new \kcmerrill\HAL\channel($app['db'], $app['log']);
/* If you wish to create a channel with a specific key, use _id() as seen below: 
   $channel->_id('hal-demo');
*/
$channel->description('The demo channel for hal!');
$channel->somekeyhere('somevaluehere');
$channel->addTo('somekeyherethatshouldbeanarray','addthisvaluetoanarray');
$channel->save();
```

```bash
# Notice /channels? Also note that you can put any keys in this json that you'd like the channel to have.
curl -d '{"name": "My new channel name!", "description": "My description for my channel goes here!}' hal.kcmerrill.com/channels
```

#### Subscriber
A subscriber within HAL is an end user that has permission to listen and subscribe to channels. A subscriber needs to have 2 keys. _id and channels. Where _id is an api key(the way the subscriber authenticates) and channels which is an array of _id's of channels the user has access to listen to. By default, _id will be generated for you.

```php
//Example creating a subscriber via php
$guest = new \kcmerrill\HAL\subscriber($app['db'], $app['log']);
$guest->_id('dave');
$guest->name('dave');
$guest->description('Dave. He who tries out the demo!');
$guest->addTo('channels','hal-demo');
$guest->save();
```

```bash
# Notice  /subscribers? Also note that you can put any keys in this json that you'd like the subscriber to have.
curl -d '{"name":"HAL","description":"Used for hal.kcmerrill.com!"}' hal.kcmerrill.com/subscribers
```

### A few things to note
* Once a socket is opened, the application would need to send in either a message right away to a channel in order to authenticate the subscriber. Once the subscriber is authenticed, only then can it start listening to channels. Or, you can simply pass in a message to a system channel. ```['h','_','hal']``` are some of the system channels. Here would be an example login message: ```{"to": "_.login", "signature":"dave"}```
* With a valid signature, you can only send to channels in which subscribers have in their channels meta data. 
