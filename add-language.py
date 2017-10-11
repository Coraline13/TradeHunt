#!/usr/bin/env python3
import argparse
import re
import json

import pyreadline.rlmain
import readline, atexit
import pyreadline.unicode_helper
import rlcompleter

with open('strings.json', 'r', encoding='utf-8') as f:
	strings_json = json.load(f)

def type_language(arg, pattern=re.compile(r"[a-z]{2}$")):
	if not pattern.match(arg):
		raise argparse.ArgumentTypeError(f"language must match {pattern}")
	return arg

parser = argparse.ArgumentParser(description='Add a new langauge translation or add missing translations to the strings file.')
parser.add_argument('language',	type=type_language, help='language/locale code')
args = parser.parse_args()

language = args.language

locales = strings_json['supported_locales']
strings = strings_json['strings']

if language in locales:
	print(f"{language} already exists, checking for missing translations...")
else:
	print(f"Adding new language {language}")
	locales.append(language)

missing = 0
added = 0
for string_name in strings:
	translations = strings[string_name]
	# look for strings missing translation
	if language not in translations:
		missing += 1
		# show existing translations
		print(f"--- set '{language}' for {string_name}")
		for el, txt in translations.items():
			print(f"  {el}: {txt}")

		# prompt for new translation
		ntxt = input(f"  {language}: ")
		if ntxt:
			# add new translation and write file
			translations[language] = ntxt
			with open('strings.json', 'w', encoding='utf-8') as f:
				json.dump(strings_json, f, sort_keys=True, indent=2, ensure_ascii=False)
				print("Saved!")
				added += 1
		else:
			print("Skipped.")

if not missing:
	print(f"All strings already have {language} translation, nothing to do...")
else:
	print(f"Lacked {missing} translations, added {added}, still missing: {missing - added}")
