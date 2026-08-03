FROM node:24-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js ./
RUN npm run build

FROM nginx:1.28-alpine
WORKDIR /var/www/html
COPY public ./public
COPY --from=assets /build/public/build ./public/build
RUN ln -sfn /var/www/html/storage/app/public ./public/storage
COPY deploy/nginx.conf /etc/nginx/conf.d/default.conf
