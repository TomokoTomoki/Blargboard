Blargboard

-------------------------------------------------------------------------------

Board software written in PHP. Uses MySQL for storage.

This is the software that powers Kuribo64 (http://kuribo64.net/). Or well, not quite.
The code provided here is a cleaned up version, with all the K64-specific stuff removed.

It is based off ABXD, which is made by Dirbaio, Nina, GlitchMr & co, and was originally
Kawa's project.

It uses Font Awesome. And possibly some other funny things I forgot about.

-------------------------------------------------------------------------------

How to install and use

First of all, if you don't have some PHP and MySQL knowledge, go find something easier
to use, like MyBB or FluxBB. Or phpBB if you're insane.

Get a webserver. Upload the Blargboard codebase to it.

Create a MySQL database and import the provided database.sql file into it.

On your webserver, open config/database.php and fill it with the right info. Open
config/salt.php and config/kurikey.php and edit them as instructed.

Browse to your freshly installed board and register.

First user to register gets full access. For this reason, avoid disclosing the board URL
before you are done installing it.

-------------------------------------------------------------------------------

Board owner's tips

http://board.example/?page=makelr -> browse there after adding categories/forums. Regenerates
the L/R tree used for subforums and such.

-------------------------------------------------------------------------------

Blargboard is provided as-is, with no guarantee that it'll be useful or even work. I'm not
responsible if it explodes in your face. Use that thing at your own risk.

Oh well, it should work rather well. See Kuribo64. But uh, we never know.

-------------------------------------------------------------------------------

Have fun.

blarg
