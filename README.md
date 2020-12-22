# Relay Bot for Setagaya Junior Orchestra

This is a LINE bot written in PHP for the Setagaya Junior Orchestra.

It grabs the messages posted in http://www.s-j-o.jp every 15 minutes, and posts it to the orchestra group LINE chat in a formatted embed.

Running this on your own requires a LINE token (and of course a bot), and a web server that:

- has PHP7 installed
- can run cronjobs on `cronjob.php`
- has tensorflow and keras (and the appropriate libraries like numpy) installed
- has enough memory to carry out the CAPTCHA bypass prediction using [`konbu`](https://github.com/yutotakano/konbu)

## Screenshots

![Demo](https://i.imgur.com/g4vehpH.png)
