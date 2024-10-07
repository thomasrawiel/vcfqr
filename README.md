# EXT:vcfqr
Create QR codes for vcf download or URLs 

## Installation
`composer require traw/vcfqr`

## Usage
Either use the available ViewHelpers or call the Service classes for your own implementation

## Settings
There are some extension settings 
- enableExamples: Makes example content elements available that show the usage of the ViewHelpers, don't forget to add the typoscript include
- storageUid: the uid of your file storage (e.g. fileadmin, leave as `0` to use the default storage)
- qrfolder: the folder name where the qr code files should be stored inside your `{storageUid}`
- qrcodeCacheLifetime: How long a file should stay cached before it is overwritten
- vcardVersion: Which version of vCard should be used (2.1, 3.0 or 4.0) - see https://de.wikipedia.org/wiki/VCard

## Available ViewHelpers
### Create QR Code for typolink

For example in a content element, add the Link/QRCode ViewHelper

```
<!-- Get the QR Code svg as inline html
<div style="width:300px;height:300px;">
    <f:format.raw>
        <vcf:link.QRCode parameter="{data.header_link}" fileName="{data.tx_vcfqr_filename}" returnValue="content"/>
    </f:format.raw>
</div>

<!-- Get the QR Code svg in an image tag -->
<f:image src="{vcf:link.qRCode(fileName: data.tx_vcfqr_filename, parameter: data.header_link, returnValue: 'url')}" width="300" height="300" />
```

- a parameter must be supplied, for example `t3://page=123 (but could be any typo3 url to pages, records, etc. as long as it is configured in your Linkhandler)
- a filename for the svg must be supplied
- returnValue must be `url` or `content` (default)

### Create QR Code to download the vCard of an address record
Place it for example inside the tt-address partials

```
<!-- Get the QR Code svg as inline html -->
<div style="width:300px;height:300px;">
    <f:format.raw>
        <vcf:address.qRCode address="{data.tx_vcfqr_address}" fileName="{data.tx_vcfqr_filename}" returnValue="content"/>
    </f:format.raw>
</div>

<!-- Get the QR Code svg in an image tag -->
<f:image src="{vcf:address.qRCode(address: data.tx_vcfqr_address, fileName: data.tx_vcfqr_filename, returnValue: 'url')}" width="300" height="300" />
```
- an address uid must be supplied
- (optional) a filename for the svg must be supplied, if none is supplied, the file will be called `{addressUid}_{currentPageUid}.svg`
- returnValue must be `url` or `content` (default)

### Create a link to download the vCard of an address record
Place it for example inside the tt-address partials

```
 <vcf:address.vcf address="{data.tx_vcfqr_address}"
                     class="vcf-download"
                     additionalAttributes="{aria-role:'button'}"
                     title="Download vCard"
                     textWrap="<span>|</span>"
    >Download vCard</vcf:address.vcf>
```
- an address uid must be supplied
- class, additionalAttributes,title,textWrap are optional and work just like in `f:link.typolink`







Tested in TYPO3 12.4.20 & TYPO3 13.4/ dev-main






