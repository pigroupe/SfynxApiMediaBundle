TmsMediaBundle API Reference
================================


API List
--------

### Media
| Method | Path                                                   | Description
|--------|--------------------------------------------------------|------------
| GET    | [/media/{reference}.{_format}](api/media/get_media.md) | get one media
| GET    | [/{reference}/{_format}.bin](api/media/get_binary.md)  | get one binary
| POST   | [/media](api/media/create_media.md)                    | Create a media
| PUT    | [/media/{reference}](api/media/update_media.md)        | update one media
| DELETE | [/media/{reference}](api/media/delete_media.md)        | delete one media

### Permission
| Method | Path                                          | Description
|--------|-----------------------------------------------|------------
| POST    | [/token](api/jwt/create_token.md) | create token permission