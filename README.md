# Tito!

Tito! is an event-based load testing tool which uses `pcntl` to run lightweight forked processes, using [ReactPHP]. It is inspired by [Locust.io].

Currently it's a proof of concept with dreams of becoming something more robust.

## Requirements

You will need the `pcntl` extension installed.

Optionally install any of the extensions suggested by `react/event-loop`. Currently not a major recommendation, but later something which replicates `libev` will be required.

One of the default tasks requires `phantomjs` be installed and in your `$PATH`.

## Run a sample

Since this is a proof of concept, all you need to do is run

```bash
php tito.php
```

## Why?

Because Locust.io beat the pants off of JMeter, Siege, etc. And I wanted to see "hm, can we do this in PHP?"

I enjoyed working directly with Python to define me tasks and scenarios, rather than XML or JMeter's UI. However, I'm not a Python developer. And I think it would be interesting to leverage libraries in the `behat` namespace.


[ReactPHP]: https://reactphp.org/
[Locust.io]: https://locust.io/
