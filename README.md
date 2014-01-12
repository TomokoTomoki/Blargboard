Blargboard

-------------------------------------------------------------------------------

Board software written in PHP. Uses MySQL for storage.

This is the software that powers Kuribo64 (http://kuribo64.net/). Or well, not quite.
The code provided here is a cleaned up version, with all the K64-specific stuff removed.

It is based off ABXD. ABXD is made by Dirbaio, Nina, GlitchMr & co, and was originally
Kawa's project.

It uses Smarty for its templates, and Font Awesome. And possibly some other funny things 
I forgot about.

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

TODO list

(no particular order there)

 * finish implementing templates
 * fix the forum editor
 * improve the permission editing interfaces
 * port the 'show/hide sidebar' feature from Kuribo64
 * make a decent homepage (including admin-editable intro blurb, latest news, and last posts)
 * reintegrate the FAQ page, and make an editor for it
 * merge/split threads a la phpBB (albeit without the shitty interface)
 * support multiple password hashing methods? (for importing from other board softwares, or for those who feel SHA256 with per-user salt isn't enough)

-------------------------------------------------------------------------------

Blargboard is provided as-is, with no guarantee that it'll be useful or even work. I'm not
responsible if it explodes in your face. Use that thing at your own risk.

Oh well, it should work rather well. See Kuribo64. But uh, we never know.

-------------------------------------------------------------------------------

Have fun.

blarg
