*AWS IMAGES BACKUP ON CLOUD*
the project idea was taken form the concept of Google Photos. Like in Google Photos one can automatically upload the images for the purpose of backup.and can retrieve them when needed.
the front-end was made on a Python framework called kivi.
the backend was made in php that is actually an API.
the API acutally send the pictures on the cloud by using the Post request and also retreives the images from the cloud by Get request when neede by the user.
the AWS cloud service was used for the storing the images on the cloud.
a bucket was sreated to store the images on xloud service.
then we have to launch an instance to manage the load on the instance.
for controlling the load on an instance load balancer was created to manage the number of hits on the current instance.
every isntance is to tun by using a key pair.
the automatic scaling was used to scale the cloud service.
the concept of scaling was used to save resources and increase the numbers of instance depending upon the  number of hits.
some of autmation scripts were also used as uploaded.
that was the description of project.
