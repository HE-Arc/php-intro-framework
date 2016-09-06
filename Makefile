pages=slides.html

all: $(pages)

%.html: %.md header.html
	pandoc -s -i \
		--mathjax \
		-f markdown \
		-t dzslides \
		-H header.html \
		--self-contained \
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
