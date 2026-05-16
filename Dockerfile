FROM nginx:alpine

# Remove default nginx page
RUN rm -rf /usr/share/nginx/html/*

# Copy your project files
COPY . /usr/share/nginx/html/

EXPOSE 80