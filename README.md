[[tinify? &input=`[+image+]` &options=`w=150,h=76,m=cover` &key=`key from https://tinypng.com`]] 

The method(m) describes the way your image will be resized. The following methods are available:

scale: Scales the image down proportionally. You must provide either a target width or a target height, but not both. The scaled image will have exactly the provided width or height.

fit:  Scales the image down proportionally so that it fits within the given dimensions. You must provide both a width and a height. The scaled image will not exceed either of these dimensions.

cover: Scales the image proportionally and crops it if necessary so that the result has exactly the given dimensions. You must provide both a width and a height. Which parts of the image are cropped away is determined automatically. An intelligent algorithm determines the most important areas and leaves these intact. 

for get key go to the https://tinypng.com