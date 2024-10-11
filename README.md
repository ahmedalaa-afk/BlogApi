# Blog Api
### Simpel Blog Api Project that providing the following features:

####  Login , Register ,Logout, Foreget and Reset Password.
#### User can update his email or password or name and can search about user by his name and see the posts that he publish.
#### Create , Update, Delete Post.
#### Post can be videos or photos or have a tags.
#### Create, Delete Comment.
#### Add, Change, Remove Emoji on Post note that there are only two emoji like and dislike.
#### Search in posts using content or tags.
#### can add post to your favorite.
#### User can follow or unfollow other users.
#### User can see his followers and who he is followings.
#### note that post and comment use slug.
## See the Documentaion on Postman:
https://apis-testers.postman.co/workspace/Ahmed's-Public-APIs~e77aa70c-4ba9-48f4-91be-2cf4be668221/collection/36516165-5783192b-e091-4a49-bfc6-c74b89caa9c2
## Usage
#### Clone the repository-
```html
git clone https://github.com/ahmedalaa-afk/BlogApi.git
```
#### Then cd into the folder with this command-
```html
cd BlogApi
```
#### Then do a composer install
```html
composer install
```
#### Then create a environment file using this command-
```html
cp .env.example .env
```
#### Then create the database-
```html
php artisan migrate
```
