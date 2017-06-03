# WP REST API - Add Hash Field to Attachments Response Object

The goal of the plugin is to add a hash value that represents each attachment. In my case I'm using the hash to look for duplicate uploads across a WordPress network. Instead of downloading every file and comparing I use the hash of the file instead. 

The plugin adds `media_file_md5` to the attachments response object along with the generated hash for it's corisponding file. 

```
{
"id": 1325,
"date": "2017-05-31T21:24:23",
"date_gmt": "2017-05-31T21:24:23",
...
"modified": "2017-06-02T21:24:57",
"modified_gmt": "2017-06-02T21:24:57",
"status": "inherit",
"type": "attachment",
...
"media_file_md5": "a81907103542d3acba7cf82966e5f605",
...
```


## Usage

1) Install
2) Activate
3) Consume via WP-API http://<example>.com/wp/v2/media/<attachment_id>
4) Retreive value via `media_file_md5` field.
