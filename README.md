# slowfoot

## install

```
git clone https://github.com/cwmoss/slowfoot.git
cd slowfoot
composer install

# develop site
php -S localhost:1199 -t mumok/ dev.php

# build site
php build.php
```

### optional

```
npm i -g groq-cli

curl -o pgclimb https://github.com/lukasmartinelli/pgclimb/releases/download/v0.3/pgclimb_darwin_amd64
chmod +x pgclimb

# strongly recommended
brew install sassc
```