# Openlynk Specification

## URLs

* It's all about URLs.
* Studyo tasks can have links to external resources.
* These links are presented in a nicely formatted button, which can either be an icon and title extracted from the resource or an embedded view of the resource.
* The icon/title or embedded view are determined from these sources, in order of precedence:
    1. Openlynk meta tags
    2. [oEmbed](http://oembed.com) provider response
    3. [Open Graph](http://ogp.me) metadata
    4. [Twitter Summary Card](https://dev.twitter.com/cards/types/summary)
    5. Agreed-upon icon and generic title
    6. The _title_ and _favicon_ of the HTML page
    7. Icon and title determined by Studyo for external services that are not part of this agreement

### Openlynk meta tags

#### Required

These `meta` tags **must** be present in the `head` of the external resource's HTML page:

* `openlynk:title`: The title of the resource as it should appear in the Studyo Task's button. Depending on the type of resource, it can be the title of the document/activity (e.g. _What are atoms?_) or a call to action (e.g. _Answer the quizz_).
* `openlynk:url`: The canonical URL of the external resource.
* `openlynk:bwIconUrl`: An image URL which represent the branding of the external resource provider in black and white. **Must** have a square aspect ratio. Will typically be displayed inside a black circle which could be fairly small (as small as 10x10 points).
* `openlynk:colorIconUrl`: An image URL which represent the branding of the external resource provider in color. **Must** have a square aspect ratio. Will typically be displayed at a size of 24x24 points.

#### Optional

These `meta` tags _may_ be present in the `head` of the external resource's HTML page:

* `openlynk:thumbnailUrl`: An image URL which represents the external resource. _Should_ have a square aspect ratio, but this is not mandatory. If a thumbnail is provided, it will be used instead of the `colorIconUrl` in the Studyo Task's button.
* `openlynk:iOSUrlScheme`: The iOS url scheme that can be used to open the resource in the host's native iOS app, if installed on the device. If the `openlynk:url` is a [Universal Link](https://developer.apple.com/library/ios/documentation/General/Conceptual/AppSearch/UniversalLinks.html#//apple_ref/doc/uid/TP40016308-CH12), the url scheme is not required.

### oEmbed

If a resource provider does not include Openlynk meta tags, the host will try an [oEmbed](http://oembed.com) request if oEmbed [discovery links](http://oembed.com/#section4) are found in the resource page.

The `thumbnail_url` and `title` oEmbed response values will be used for the Studyo Task's button icon and title.

If `type` is `video` or `rich`, Studyo _may_ choose to display the embed `html` code inside the task, instead of the usual "icon and title" presentation.

### Open Graph

If previous options are not available, the host will look for [Open Graph](http://ogp.me) metadata on the resource page.

The `og:image` and `og:title` values will be used for the Studyo Task's button icon and title.

### Twitter Summary Card

If previous options are not available, the host will look for [Twitter Summary Card](https://dev.twitter.com/cards/types/summary) definition on the resource page.

The `twitter:image` and `twitter:title` values will be used for the Studyo Task's button icon and title.

### Agreed-upon icon and generic title

If no metadata of any sort is found on the resource page, either because the provider does not implement them or because the link requires authenticated access and returns an error page when fetched without proper authentication, the icons (bw and color) provided by the provider will be used in the host, with a generic call to action title (e.g. _Open in Netmath_). The icon and title will be determined based on the domain of the resource URL (e.g. *.netmath.com means the host should use the Netmath icon and title).

### Fallback

If a link points to a domain that is not part of this agreement, therefore has no predefined icon and title, the host _may_ opt to use the provider's icon and name on a case-by-case basis. If not, it will fall back to using the `<title>` and _favicon_ of the page, which can be one of:

* The value of the `<link rel="icon">` tag in the `<head>` of the page
* The value of the `<link rel="apple-touch-icon">` tag in the `<head>` of the page
* The image located at the `/favicon` location on the resource host

## Openlynk Resource Picker

* As mentioned above: it's all about URLs.
* So simply pasting a URL is enough to create a branded button inside a Studyo task.
* But to simplify the process of adding a URL to a task, the provider can implement a _picker_ UI.
* When a provider implements a picker, it will show up in Studyo's list of supported providers (e.g. _Attach a ChallengeU activity_)
* The UI of the picker is implemented by the provider using HTML, CSS and JavaScript.
* Studyo will display the picker in a popup/modal window.
* Studyo will navigate to an agreed-upon URL for the picker (e.g. `http://netmath.com/services/openlynk/picker`)
* If the provider requires authentication and there is no active browser session, a login UI should be displayed first.
* The provider should implement a UI flow that is appropriate for its type of content (multi-level hierarchical navigation, topic selection, etc).
* Once a resource is selected by the user, the picker must return the URL and some metadata to the host using the mechanisms documented below.

### Returning the selected URL form the picker

There are various ways a picker can return the selected URL to the host: `postMessage` or `url`.

#### postMessage

* The picker will be invoked with the `callbackType=postMessage` query string.
* Once the user has selected a resource, the picker must call the `window.postMessage` JavaScript function to send the select resource to the host:

```javascript
var message = {
    type: "StudyoPicker_PickedResource", // Required, verbatim
    resource: {
        url: "http://netmath.com/123",
        title: "Arithmetic",
        colorIconUrl: "http://netmath.com/colorIcon.png",
        bwIconUrl: "http://netmath.com/bwIcon.png",
        thumbnailUrl: null // Optional
    }
};

window.opener.postMessage(message, "*"); // TODO: Do not use a wildcard target origin
```

* The host will take care of closing the picker popup, so the picker must not call `window.close()` itself.
* Each key in the `resource` object correspond to an _Openlynk meta tag_. Please refer to the _Openlynk meta tags_ documentation above for a detailed description.

#### url

* The picker will be invoked with the `callbackType=url&callbackUrl=x` query string.
* Once the user has selected a resource, the picker must navigate to the provided `callbackUrl`, providing the resource via the query string:

```javascript
var resource = {
    url: "http://netmath.com/123",
    title: "Arithmetic",
    colorIconUrl: "http://netmath.com/colorIcon.png",
    bwIconUrl: "http://netmath.com/bwIcon.png",
    thumbnailUrl: null // Optional
};

window.location.assign(callbackUrl + "?" + $.param(resource));
```

### Native iOS picker via url scheme or universal links

* If the provider only has a native iOS app (no webapp), the agreed-upon link to the picker _must_ be a url scheme that opens the native app to display the picker. 
    * In that case, `callbackType` will always be `url` and the `callbackUrl` will be a url scheme handled by the host.
* If the provider has both a native iOS app and a webapp, the agreed-upon link _should_ be a [Universal Link](https://developer.apple.com/library/ios/documentation/General/Conceptual/AppSearch/UniversalLinks.html#//apple_ref/doc/uid/TP40016308-CH12).
    * In that case, when the host is runnning on iOS and the native host app is installed on the iOS device, the native picker will be displayed.
    * If the host app is not installed, the web picker will be displayed.
