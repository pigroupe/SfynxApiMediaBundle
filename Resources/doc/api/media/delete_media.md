# API: [DELETE] Media

Delete a media

## General request

|             | Values
|-------------|-------
| **Method**  | DELETE
| **Route**   | /media/{reference}
| **Secured** | true


## Route Parameters description

- *reference* : The unique reference of the media

## HTTP Response codes

| Code | HTTP Status Code       | Description
|------|------------------------|------------
| 204  | No Content             | if a correct reference is passed
| 404  | Not found (wrong id)   | if an invalid reference (i.e a reference which does not exist neither in the database or in the filesystem)

## Example of usage

```curl
curl -X DELETE http://your_domain/api/media/reference
```