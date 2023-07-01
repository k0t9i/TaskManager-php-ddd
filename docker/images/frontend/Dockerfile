FROM node:lts-alpine

WORKDIR /app

COPY ./src-frontend/package*.json ./

RUN npm install

COPY ./src-frontend .

ENTRYPOINT [ "npm", "run", "dev" ]