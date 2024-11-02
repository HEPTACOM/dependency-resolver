SHELL := /bin/bash

.DEFAULT_GOAL := help
.PHONY: help
help: ## List useful make targets
	@echo 'Available make targets'
	@grep -E '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | sort | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-30s\033[0m %s\n", $$1, $$2}'

CI_VAR_DIR := .build
$(CI_VAR_DIR):
	mkdir -p "$(CI_VAR_DIR)"

CI_JUNIT_OUTPUT_DIR := $(CI_VAR_DIR)/junit
$(CI_JUNIT_OUTPUT_DIR): $(CI_VAR_DIR)
	mkdir -p "$(CI_JUNIT_OUTPUT_DIR)"

#######
# php #
#######
PHP := "$(shell which php)"
PHP_ARGS := -d "error_reporting=E_ALL&~E_DEPRECATED"
PHP_X := $(PHP) $(PHP_ARGS)

############
# composer #
############
COMPOSER_FILE := "$(shell which composer)"
COMPOSER_ARGS := -vv --no-interaction
COMPOSER_X := $(PHP_X) "$(COMPOSER_FILE)" $(COMPOSER_ARGS)

vendor:
	$(COMPOSER_X) install

########
# curl #
########
CURL := "$(shell which curl)"
CURL_ARGS :=
CURL_X := "$(CURL)" $(CURL_ARGS)

######
# jq #
######
JQ := "$(shell which jq)"
JQ_ARGS :=
JQ_X := "$(JQ)" $(JQ_ARGS)

JSON_FILES := $(shell find . -name '*.json' -not -path './vendor/*' -not -path './.dev-ops/bin/*/vendor/*' -not -path './$(CI_VAR_DIR)/*')
JSON_FILES__CHECK := $(JSON_FILES:%=%__CHECK)

.PHONY: cs-jq
cs-jq: $(JSON_FILES__CHECK) ## Run jq on every json file to ensure they are parsable and therefore valid

.PHONY: $(JSON_FILES__CHECK)
$(JSON_FILES__CHECK):
	$(JQ) . $(subst __CHECK,,$@)

############
# xsltproc #
############
XSLTPROC := "$(shell which xsltproc)"
XSLTPROC_ARGS :=
XSLTPROC_X := "$(XSLTPROC)" $(XSLTPROC_ARGS)

######################
# composer-normalize #
######################
COMPOSER_NORMALIZE_DOWNLOAD := https://github.com/ergebnis/composer-normalize/releases/download/2.42.0/composer-normalize.phar
COMPOSER_NORMALIZE_FILE := .dev-ops/bin/composer-normalize
COMPOSER_NORMALIZE_ARGS := --no-check-lock --no-update-lock
COMPOSER_NORMALIZE_X := $(PHP_X) "$(COMPOSER_NORMALIZE_FILE)" $(COMPOSER_NORMALIZE_ARGS)

COMPOSER_JSON_FILES := $(shell find . -name 'composer.json' -not -path './vendor/*' -not -path './.dev-ops/bin/*/vendor/*' -not -path './$(CI_VAR_DIR)/*')
COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_CHECK := $(COMPOSER_JSON_FILES:%=%__COMPOSER_NORMALIZE_CHECK)
COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_FIX := $(COMPOSER_JSON_FILES:%=%__COMPOSER_NORMALIZE_FIX)

$(COMPOSER_NORMALIZE_FILE): ## Install composer-normalize executable
	$(CURL) -L "$(COMPOSER_NORMALIZE_DOWNLOAD)" -o "$(COMPOSER_NORMALIZE_FILE)"

.PHONY: cs-composer-normalize
cs-composer-normalize: $(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_CHECK) ## Run composer-normalize for composer.json style analysis

.PHONY: $(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_CHECK)
$(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_CHECK): vendor $(COMPOSER_NORMALIZE_FILE)
	$(COMPOSER_NORMALIZE_X) --diff --dry-run $(subst __COMPOSER_NORMALIZE_CHECK,,$@)

.PHONY: cs-fix-composer-normalize
cs-fix-composer-normalize: vendor $(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_FIX) ## Run composer-normalize for automatic composer.json style fixes

.PHONY: $(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_FIX)
$(COMPOSER_JSON_FILES__COMPOSER_NORMALIZE_FIX): vendor $(COMPOSER_NORMALIZE_FILE)
	$(COMPOSER_NORMALIZE_X) --diff $(subst __COMPOSER_NORMALIZE_FIX,,$@)

########
# pint #
########
PINT_DIR := .dev-ops/bin/pint
PINT_FILE := "$(PINT_DIR)/vendor/bin/pint"
PINT_CONFIG_FILE := .dev-ops/pint.json
PINT_ARGS := --config="$(PINT_CONFIG_FILE)"
PINT_X := $(PHP_X) $(PINT_FILE) $(PINT_ARGS)

$(PINT_FILE): ## Install Pint executable
	$(COMPOSER_X) install -d .dev-ops/bin/pint

.PHONY: cs-style
cs-style: vendor $(CI_JUNIT_OUTPUT_DIR) $(PINT_FILE) ## Run Pint for code style analysis
	[[ -z "${CI}" ]] || $(PINT_X) --test --format=junit > "$(CI_JUNIT_OUTPUT_DIR)/style.junit.xml"
	[[ -n "${CI}" ]] || $(PINT_X) --test

.PHONY: cs-fix-style
cs-fix-style: vendor $(CI_JUNIT_OUTPUT_DIR) $(PINT_FILE) ## Run Pint for automatic code style fixes
	[[ -z "${CI}" ]] || $(PINT_X) --format=junit > "$(CI_JUNIT_OUTPUT_DIR)/fix-style.junit.xml"
	[[ -n "${CI}" ]] || $(PINT_X)

#########
# phpmd #
#########
PHPMD_JUNIT_DOWNLOAD := https://phpmd.org/junit.xslt
PHPMD_JUNIT_XSLT := .dev-ops/phpmd-junit.xslt
PHPMD_PHAR := https://github.com/phpmd/phpmd/releases/download/2.15.0/phpmd.phar
PHPMD_CONFIG_FILE := .dev-ops/phpmd.xml
PHPMD_FILE := .dev-ops/bin/phpmd

$(PHPMD_FILE): ## Install phpmd executable
	$(CURL) -L "$(PHPMD_PHAR)" -o "$(PHPMD_FILE)"

$(PHPMD_JUNIT_XSLT): ## Install phpmd jUnit converter
	$(CURL) -L "$(PHPMD_JUNIT_DOWNLOAD)" -o "$(PHPMD_JUNIT_XSLT)"

.PHONY: cs-phpmd
cs-phpmd: vendor $(CI_JUNIT_OUTPUT_DIR) $(PHPMD_FILE) $(PHPMD_JUNIT_XSLT) ## Run php mess detector for static code analysis
	$(PHP_X) $(PHPMD_FILE) --ignore-violations-on-exit src ansi "$(PHPMD_CONFIG_FILE)"
	$(PHP_X) $(PHPMD_FILE) src xml "$(PHPMD_CONFIG_FILE)" | $(XSLTPROC) "$(PHPMD_JUNIT_XSLT)" - > "$(CI_JUNIT_OUTPUT_DIR)/php-md.junit.xml"

###########
# phpstan #
###########
PHPSTAN_DIR := .dev-ops/bin/phpstan
PHPSTAN_FILE := "$(PHPSTAN_DIR)/vendor/bin/phpstan"
PHPSTAN_CONFIG_FILE := .dev-ops/phpstan.neon
PHPSTAN_ARGS :=
PHPSTAN_X := $(PHP_X) "$(PHPSTAN_FILE)" $(PHPSTAN_ARGS)

$(PHPSTAN_FILE): ## Install phpstan executable
	$(COMPOSER_X) install -d "$(PHPSTAN_DIR)"

.PHONY: cs-phpstan
cs-phpstan: vendor $(CI_JUNIT_OUTPUT_DIR) $(PHPSTAN_FILE) ## Run phpstan for static code analysis
	[[ -z "${CI}" ]] || $(PHPSTAN_X) analyse -c "$(PHPSTAN_CONFIG_FILE)" --error-format=junit > "$(CI_JUNIT_OUTPUT_DIR)/phpstan.junit.xml"
	[[ -n "${CI}" ]] || $(PHPSTAN_X) analyse -c "$(PHPSTAN_CONFIG_FILE)"

###########
# phpunit #
###########
PHPUNIT_DIR := .dev-ops/bin/phpunit
PHPUNIT_FILE := "$(PHPUNIT_DIR)/vendor/bin/phpunit"
PHPUNIT_ARGS := --config=tests/phpunit.xml
PHPUNIT_X := $(PHP_X) $(PHPUNIT_FILE) $(PHPUNIT_ARGS)

$(PHPUNIT_FILE): ## Install phpunit executable
	$(COMPOSER_X) install -d "$(PHPUNIT_DIR)"

.PHONY: test
test: vendor $(CI_VAR_DIR) $(PHPUNIT_FILE) ## Run phpunit tests
	[[ -z "${CI}" ]] || $(PHPUNIT_X) --log-junit="$(CI_JUNIT_OUTPUT_DIR)/phpunit.junit.xml"
	[[ -n "${CI}" ]] || $(PHPUNIT_X)

###

.PHONY: clean
clean: ## Cleans up all ignored files and directories
	[[ ! -d vendor ]] || rm -rf vendor
	[[ ! -f "$(COMPOSER_NORMALIZE_FILE)" ]] || rm -f "$(COMPOSER_NORMALIZE_FILE)"
	[[ ! -f "$(PHPMD_FILE)" ]] || rm -f "$(PHPMD_FILE)"
	[[ ! -f "$(PHPMD_JUNIT_XSLT)" ]] || rm -f "$(PHPMD_JUNIT_XSLT)"
	[[ ! -d "$(PHPSTAN_DIR)/vendor" ]] || rm -rf "$(PHPSTAN_DIR)/vendor"
	[[ ! -d "$(PHPUNIT_DIR)/vendor" ]] || rm -rf "$(PHPUNIT_DIR)/vendor"
	[[ ! -d "$(PINT_DIR)/vendor" ]] || rm -rf "$(PINT_DIR)/vendor"
	[[ ! -d "$(CI_JUNIT_OUTPUT_DIR)" ]] || rm -rf "$(CI_JUNIT_OUTPUT_DIR)"
	[[ ! -d "$(CI_VAR_DIR)" ]] || rm -rf "$(CI_VAR_DIR)"
