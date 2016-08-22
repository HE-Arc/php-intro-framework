pages=README.html

all: $(pages)

%.html: %.md header.html
	# For distribution, enable: --self-contained
	pandoc -s -i \
		--mathjax \
		-f markdown \
		-t dzslides \
		-H header.html \
		--variable title-prefix=HE-Arc \
		-o $@ \
		$^
		

%.pdf: %.md
	pandoc --latex-engine=xelatex \
		-f markdown \
		-t latex \
		-o $@ \
		$^

clean:
	rm -f $(pages) *.pdf

.PHONY: clean
