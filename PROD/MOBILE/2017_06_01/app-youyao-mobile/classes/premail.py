#!/usr/bin/env python
# -*- coding: utf-8 -*-
import os
import sys
import codecs
from premailer import transform

if len(sys.argv) < 2 or not os.path.isfile(sys.argv[1]):
    sys.exit(0)

fh = codecs.open(sys.argv[1], 'r', 'utf-8')

target = codecs.open(sys.argv[1] + ".converted", 'w', 'utf-8')
target.write(transform(fh.read()))
target.close()
