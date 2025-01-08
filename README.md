# block_access
This is a plugin to secure your Joomla website. With this plugin you can block access to the frontend, the backend or both.

## setup
1. After installation of the plugin and enabling it, go to _Extensions -> Plugins -> System - Block Access_
2. Set the security key. This is the "main" key. This grants you access to the blocked area.
  ![image](https://github.com/user-attachments/assets/cdb887be-93e0-47ca-8725-200c14105fa8)

3. Optional: Set the alternative security key for the frontend. When this key is set, the frontend will be accessible only with this key, the backend still uses the main security key

4. Choose the area you want to block. _All_ blocks both administrator and site. _All_ uses only the main security key!
5. Chooe a type of block. **ATTENTION:** Setting this to _Redirect_ and choosing _Site_ or _All_ as area to block has one special effect: If you choose a Joomla-internal address, this address will be accessible! If this effect would not exist, the browser would throw an error (ERR_TOO_MANY_REDIRECTS). Imagine this: You set `/my/redirect/path/` (which is a Joomla-internal site) as redirect URL. When the user accesses `/`, they gets redirected to the configured address. There the plugin would take action again and would send the user (again) to the redirect URL. And again and again and again..... So: Either you choose an address outside of Joomla or you remember that any redirect URL which is Joomla-internal will be accessed and is therefore not blocked! 
