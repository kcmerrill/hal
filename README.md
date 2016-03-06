# Quick Summary
If you're familiar with [pusher.com](pusher.com) then HAL should be somewhat familiar. Based on a pub sub system, one can send in web socket events and also curl into HAL to send events to those that are listening to subscribed channels. 

There are a few things to note regarding HAL before continuing. HAL subscribes to K.I.S.S philosophy in `Keeping it Simple Stupid`. HAL is not meant to be an authentication system, nor is it used in storing and saving messages/channels etc. The idea is, whatever application you choose to build on top of HAL stores all of this information. Which means:

* You will not find the saving of messages
* User state is not saved(meaning you have to reload users when you reload HAL)
* HAL does not know what channel meta data is(ie: Channel names, members etc ...). 
* Only HAL has the ability to create users and channels. 

The idea is that the above are handled by your application. If you wish to save messages, or add authentication on top of HAL that would be up to you and your application. 

###Usage
```hal --web 80 --socket 8080 --signature halsignature```
Both `web` and `socket` ports can be configured to be whatever you choose, along with `signature`. Be mindful that `signature` is hal's master signature and is the only user that can create users and channels. 

###Key concepts
Hal accepts a JSON string as a message. Each message should consist of a `to`, `msg`, and a `signature` field. The `to` field can either be a `#channel` or a `@username` in which to send in a message. The `msg` field can be any payload you wish. String, Text, that's up to you and the application you're trying to build. `signature` his the token you'll use to authenticate the user sending in the message. 

Here is a sample message assuming hal's signature is set to `halsignature`:

```
curl -d '{"to": "_", "msg": "/register kcmerrill kcsignature", "signature": "halsignature"}' localhost:80
```

This message just so happens to register a new user `@kcmerrill` with a signature(auth token) of `kcsignature`. Lets say you wanted to send a message to `@kcmerrill` then you would use the following message:

```
curl -d '{"to": "@kcmerrill", "msg": "This is my message to @kcmerrill!", "signature": "halsignature"}' localhost:80
```

Need to send a message to a specific channel? Notice `kcsignature`? This is sending as the user `@kcmerrill`
```
curl -d '{"to": "#channelname", "msg": "This is my message to #channelname", "signature": "kcsignature"}' localhost:80
```

Of course, you can use websockets too! 

If you've connected to HAL's websocket port, `default: 8080` you can simply passin a JSON string much like we've done for the above examples.

Here is a sample javascript code as an example:
```
   <script>
        var ws = new WebSocket('ws://localhost:8080');
        ws.onopen = function(e){
            ws.send('{"to": "@kcmerrill", "msg": "This is my message to @kcmerrill!", "signature": "halsignature"}');
        };
        ws.onmessage = function(e) {
            if(e && e.data) {
                var d = JSON.parse(e.data);
                alert(d.msg);
            }
        };
   </script>
```
