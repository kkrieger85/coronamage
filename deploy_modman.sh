#!/usr/bin/env bash
for file in .modman/*; do
  if [ -d "$file" ]; then
    module=$(echo $file | sed 's/.modman\///g')
    modman deploy $module --copy --force
  fi
done
