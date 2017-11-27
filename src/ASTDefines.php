<?php

namespace GenDiff\ASTDefines;

const STATE_ADDED = 0x00000001;
const STATE_REMOVED = 0x00000010;
const STATE_UPDATED = 0x00000100;
const STATE_IDENTICAL = 0x00001000;
const STATE_NESTED_BEFORE = 0x00010000;
const STATE_NESTED_AFTER = 0x00100000;

const RESPONSE_SPACES_NEXT_LEVEL = 4;

const KEY_KEY = 'key';
const KEY_STATE = 'state';
const KEY_DATA_BEFORE = 'dataBefore';
const KEY_DATA_AFTER = 'dataAfter';
const KEY_LABEL = 'label';

const FORMAT_PRETTY = 'pretty';
const FORMAT_PLAIN = 'plain';
