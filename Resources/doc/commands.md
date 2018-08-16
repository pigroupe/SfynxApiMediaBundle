# Commands

> #### Display or remove media without associated files

**use the command below:**

```sh
$ php app/console sfynx-media:clean:no-file [-f|--force] [-l|--limit="..."] [-o|--offset="..."]
```

**with specific configuration**

| Name      | Optional | Default  | Requirements | Description
|-----------|----------|----------|--------------|------------
| limit     | false    | 10000    | int          | The limit to processed
| offset    | false    | 0        | int          | The offset to processed
| force     | true     | No Value |              | if a media file is missing, the entity will be removed

> #### Move media files following to their metadata

**use the command below:**

```sh
$ php app/console sfynx-media:move [-p|--provider="..."] [-f|--force] [-l|--limit="..."] [-o|--offset="..."]
```

**with specific configuration**

| Name      | Optional | Default       | Requirements | Description
|-----------|----------|---------------|--------------|------------
| provider  | false    | default_media | string       | The media provider service name
| limit     | false    | 10000         | int          | The limit to processed
| offset    | false    | 0             | int          | The offset to processed
| force     | true     | No Value      |              | if present, the files will be moved

> #### Updates the existing media with ones in the specified folder

**use the command below:**

```sh
$ php app/console sfynx-media:replace [--path="..."] [-r|--recursive=...] [--extension="..."]
```

**with specific configuration**

| Name      | Optional | Default   | Requirements | Description
|-----------|----------|-----------|--------------|------------
| path      | false    |           | string       | The folder to look through
| recursive | true     | -1        | int          | Look through sub-folders as well? (recursive)
| extension | true     | *         | string       | File extension to look for

> #### Import media to csv file

**use the command below:**

```sh
$ php app/console sfynx-media:import [-w|--with-header] [-d|--delimiter="..."]
[-c|--enclosure="..."] [-p|--provider="..."] [-b|--batch="..."] filePath
```

**with specific configuration**

| Name        | Optional | Default       | Requirements | Description
|-------------|----------|---------------|--------------|------------
| filePath    | false    |               | string       | The file path to use
| with-header | false    | No value      |              | Add this option if the CSV file contains a header
| delimiter   | false    | ,             | string       | The csv delimiter
| enclosure   | false    | "             | string       | The csv enclosure
| provider    | false    | batch_media   | string       | The media provider service name
| batch       | false    | 1             | int          | To execute the import in batch mode
