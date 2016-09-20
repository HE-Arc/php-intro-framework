SOURCES = slides.md
SLIDES = slides.html
PDFS = slides.pdf

.PHONY: all
all: html pdf

.PHONY: html
html: $(SLIDES)

.PHONY: pdf
pdf: $(PDFS)

$(SLIDES): %.html: %.md theme.html
	pandoc -s -i \
		--mathjax \
		-f markdown \
		-t dzslides \
		-H theme.html \
		--self-contained \
		--variable title-prefix=HE-Arc \
		-o $@ \
		$^
		

$(PDFS): %.pdf: %.md
	pandoc --latex-engine=xelatex \
		-f markdown \
		-t latex \
		-H theme.tex \
		--variable papersize=a4 \
		--variable fontsize=12pt \
		--variable mainfont="Linux Libertine O" \
		--variable sansfont="Linux Biolinum O" \
		--variable monofont="Inconsolata" \
		--variable monofontoptions="Scale=0.9" \
		--variable linkcolor="blue" \
		--variable urlcolor="blue" \
		-o $@ \
		$^

.PHONY: clean
clean:
	rm -f $(PDFS) $(SLIDES)
