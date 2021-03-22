# Face Rekognition Cropping - WordPress Plugin

Automatically create new image crops with faces recognised by AWS Rekognition.

This plugin uses the AWS Rekognition API (without the AWS SDK) to recognize faces in images uploaded to the Media Library.

The plugin will automatically create a version of your uploaded images with the faces centered in the image to allow for better crops using theme sizes. This is a new entry in the media library and will not replace the original upload.

Optionally, enable automatic generation of individual images in the Media Library from faces recognized in an image.

Potential issues:

* Rekognition only allows JPG and PNG images, up to 5MB in size when uploaded using encoded bytes.
* Rekognition will by default recognize up to 100 faces. The plugin has this set to 5 faces by default; feel free to adjust up to a 100.

Future ideas:

* Settings to specify the "shape" for new derived images from faces. E.g. Square, Portrait, Landscape, Dynamic.
* Zoom level to scale individually recognized faces.
