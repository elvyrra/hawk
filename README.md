# HAWK
Hawk is a platform that can be used to generate your Web application. The goal of this engine is to be easy to use for simple users who want to create their own application to manage their teams, projects, stocks, and for developpers that want to develop their own plugins and themes.

## Install
Hawk installation is very easy to perform. There are two ways to install Hawk :

### Easy way
1. Download the zip file containing the project
2. Extract the zip content on your web server
3. Type your site URL in your favorite browser, and fill the displayed forms

### Developer / Sysadmin way
1. Go on your root directory
2. Type ```git clone  https://github.com/elvyrra/hawk.git ```
3. Type your site URL in your favorite browser, and fill the displayed forms

## User guide
The full user guide is available on : [Hawk App](http://hawk-app.fr/#!/guide-utilisateur)

## Dev guide
The full developer guide is available on : [Wiki](https://github.com/elvyrra/hawk/wiki)

# Changeset
## v2.1.0
* Add DB::sqlExpression method to insert raw SQL expression in DBExamples or in insert, update and replace methods, without binding the value
* Performances increasing removing automatic build of HTML output into phpQuery instance in Controller class

## v2.0.0
* Full rebuild of Hawk engine, around middlewares system
* Compatibility with ES5 browsers

## v1.6.3
* Change method Response::redirectToAction for Response::redirectToRoute
* Add support of categories in autocomplete

## v1.6.2.7
* Fix bug in the theme 'Hawk' to close dialog boxes
* Compute body in PATCH / PUT / DELETE methods