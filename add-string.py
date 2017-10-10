#!/usr/bin/env python3
import argparse
import re
import json

with open('strings.json', 'r', encoding='utf-8') as f:
	strings_json = json.load(f)

with open('lib/strings.php', 'r', encoding='utf-8') as f:
	php_code = f.read()

define_insert_pos = -1;
highest_constant = -1;
php_constants = set()
for match in re.finditer(re.compile(r"define\s*\(\s*\"(STRING_[A-Z0-9_]+)\"\s*,\s*(\d+)\)\s*;"), php_code):
	highest_constant = max(highest_constant, int(match.group(2)))
	define_insert_pos = max(define_insert_pos, match.end())
	php_constants.add(match.group(1))

if define_insert_pos < 0 or highest_constant < 0:
	raise ValueError("failed to find STRING defines in lib/strings.php")

string_code_to_name = re.search("\$string_code_to_name = array\((?:\s*\w+\s*=>\s*\"\w+\"\s*,)*\s*(\w+\s*=>\s*\"\w+\")?\s*(\);)", php_code)
print(f"string_code_to_name - trailing comma: {bool(string_code_to_name.group(1))}, closing paranthesis at {string_code_to_name.start(2)}")

if not string_code_to_name:
	raise ValueError("failed to find $string_code_to_name declaration in lib/strings.php")

def type_string_name(arg, pattern=re.compile(r"^[a-z](?:[a-z0-9_]*[a-z0-9])?$")):
	constant_name = f"STRING_{arg.upper()}"
	if not pattern.match(arg):
		raise argparse.ArgumentTypeError(f"string_name must match {pattern}")
	if constant_name in php_constants:
		raise argparse.ArgumentTypeError(f"{constant_name} already defind in lib/strings.php")
	if arg in strings_json['strings']:
		raise argparse.ArgumentTypeError(f"{arg} already exists in strings.json")
	return arg

parser = argparse.ArgumentParser(description='Add an internationalized string.')
parser.add_argument('string_name',	type=type_string_name, help='string name, the php constant will be named STRING_{STRING_NAME} and the constant in strings.json will be named as given')
args = parser.parse_args()

string_name = args.string_name
constant_name = f"STRING_{string_name.upper()}"
print(f"Adding {string_name} (php: {constant_name})")

locales = strings_json['supported_locales']
translations = {}
for locale in locales:
	translations[locale] = input(f"{locale}: ");

strings_json['strings'][string_name] = translations

# add constant definition
constant_definition = f'\ndefine("{constant_name}", {highest_constant + 1});'
php_code = php_code[:define_insert_pos] + constant_definition + php_code[define_insert_pos:]

# add entry at end of $string_code_to_name
string_code_to_name = re.search("\$string_code_to_name = array\((?:\s*\w+\s*=>\s*\"\w+\"\s*,)*\s*(\w+\s*=>\s*\"\w+\")?\s*(\);)", php_code)
array_entry = f'    {constant_name} => "{string_name}",\n'
add_comma = bool(string_code_to_name.group(1))
if add_comma:
	# must add trailing comma to last array entry
	php_code = php_code[:string_code_to_name.start(1)] + string_code_to_name.group(1) + ',' + php_code[string_code_to_name.end(1):]

array_entry_insert_pos = string_code_to_name.start(2) + int(add_comma)
php_code = php_code[:array_entry_insert_pos] + array_entry + php_code[array_entry_insert_pos:]


with open('strings.json', 'w', encoding='utf-8') as f:
	json.dump(strings_json, f, sort_keys=True, indent=2, ensure_ascii=False)

with open('lib/strings.php', 'w', encoding='utf-8') as f:
	f.write(php_code)
