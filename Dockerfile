#Using nginx alpine image
FROM nginx:alpine
# Set the working directory
WORKDIR /usr/share/nginx/html
# Remove default nginx static files
RUN rm -rf *
# Copy static files from the local directory to the nginx html directory
#COPY /home/ec2-user/myweb/* /usr/share/nginx/html
COPY . .
# Expose port 80 to the outside world
EXPOSE 80
#Start nginx server
CMD ["nginx", "-g", "daemon off;"]
#Add owner label
LABEL owner="avinash"