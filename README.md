# export-sms-thread-to-html

Export a clean human readable HTML page from a SMS export. The export has to be made by [SMS Import / Export ](https://github.com/tmo1/sms-ie). The zip file must be extracted on the computer.

MMS are converted to simple text. Images are copied to a directory `export_data` next to the html page.

## Installation 

```shell

cd ~
git clone https://gitea.nacq.me/nicolas/export-sms-thread-to-html.git
export-sms-thread-to-html

```

## Usage

```shell
cd ~/export-sms-thread-to-html
php export-sms-thread-to-html.php [params]
```

### Params
 - path to ndjson file
 - path to data dir
 - thread_id
 - HTML file output path
 - date from : optional
 - date to : optional

### Example
```shell
cd ~/export-sms-thread-to-html
# export all messages after 2023-05-07
php export-sms-thread-to-html.php ~/downloads/export/ 601234567 export.html 2023-05-07 

# export year 2022
php export-sms-thread-to-html.php ~/downloads/export/ 601234567 export.html 2022-01-01 2022-12-31 


## result

![Capture 1](capture_1.jpg)

![Capture 2](capture_2.jpg)