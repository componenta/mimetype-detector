<?php

declare(strict_types=1);

namespace Componenta\Detector\MimeTypes;

enum Text: string
{
    // Common
    case Plain = 'text/plain';
    case Html = 'text/html';
    case Css = 'text/css';
    case Csv = 'text/csv';
    case Tsv = 'text/tab-separated-values';
    case Javascript = 'text/javascript';
    case Xml = 'text/xml';
    case XmlExternal = 'text/xml-external-parsed-entity';
    
    // Markup
    case Markdown = 'text/markdown';
    case XMarkdown = 'text/x-markdown';
    case Asciidoc = 'text/asciidoc';
    case Rst = 'text/x-rst';
    case Org = 'text/x-org';
    case Textile = 'text/x-textile';
    case Troff = 'text/troff';
    case Rtx = 'text/richtext';
    case Sgml = 'text/sgml';
    
    // Programming languages
    case Php = 'text/x-php';
    case Python = 'text/x-python';
    case Ruby = 'text/x-ruby';
    case Perl = 'text/x-perl';
    case Java = 'text/x-java';
    case JavaSource = 'text/x-java-source';
    case CSource = 'text/x-c';
    case CppSource = 'text/x-c++src';
    case CHeader = 'text/x-c++hdr';
    case CSharp = 'text/x-csharp';
    case Go = 'text/x-go';
    case Rust = 'text/x-rust';
    case Swift = 'text/x-swift';
    case Kotlin = 'text/x-kotlin';
    case Scala = 'text/x-scala';
    case Groovy = 'text/x-groovy';
    case Clojure = 'text/x-clojure';
    case Lua = 'text/x-lua';
    case Tcl = 'text/x-tcl';
    case Fortran = 'text/x-fortran';
    case Pascal = 'text/x-pascal';
    case Haskell = 'text/x-haskell';
    case Erlang = 'text/x-erlang';
    case Elixir = 'text/x-elixir';
    case Ocaml = 'text/x-ocaml';
    case Fsharp = 'text/x-fsharp';
    case Lisp = 'text/x-lisp';
    case Scheme = 'text/x-scheme';
    case Racket = 'text/x-racket';
    case Prolog = 'text/x-prolog';
    case Cobol = 'text/x-cobol';
    case Asm = 'text/x-asm';
    case Nasm = 'text/x-nasm';
    case Llvm = 'text/x-llvm';
    case Nim = 'text/x-nim';
    case Zig = 'text/x-zig';
    case Julia = 'text/x-julia';
    case R = 'text/x-r';
    case Matlab = 'text/x-matlab';
    case ObjectiveC = 'text/x-objectivec';
    case Dart = 'text/x-dart';
    case TypeScript = 'text/typescript';
    case Tsx = 'text/tsx';
    case Jsx = 'text/jsx';
    case CoffeeScript = 'text/coffeescript';
    case XCoffeeScript = 'text/x-coffeescript';
    
    // Scripting
    case XSh = 'text/x-sh';
    case XBash = 'text/x-bash';
    case XZsh = 'text/x-zsh';
    case XFish = 'text/x-fish';
    case XPowershell = 'text/x-powershell';
    case XCmd = 'text/x-cmd';
    case XBat = 'text/x-bat';
    case XMakefile = 'text/x-makefile';
    case XCmake = 'text/x-cmake';
    case XAnt = 'text/x-ant';
    case XMaven = 'text/x-maven';
    case XGradle = 'text/x-gradle';
    case XDockerfile = 'text/x-dockerfile';
    case XVagrantfile = 'text/x-vagrantfile';
    
    // Config
    case XYaml = 'text/x-yaml';
    case XToml = 'text/x-toml';
    case XProperties = 'text/x-properties';
    case XIni = 'text/x-ini';
    case XDotenv = 'text/x-dotenv';
    case XNginx = 'text/x-nginx-conf';
    case XApache = 'text/x-apache-conf';
    case XSystemd = 'text/x-systemd-unit';
    case XEditorconfig = 'text/x-editorconfig';
    case XGitignore = 'text/x-gitignore';
    case XGitattributes = 'text/x-gitattributes';
    case XNpmrc = 'text/x-npmrc';
    case XYarnrc = 'text/x-yarnrc';
    
    // Data / Query
    case XSql = 'text/x-sql';
    case XGraphql = 'text/x-graphql';
    case XSparql = 'text/x-sparql-query';
    case XCypher = 'text/x-cypher-query';
    
    // Template engines
    case XTwig = 'text/x-twig';
    case XBlade = 'text/x-blade';
    case XJinja = 'text/x-jinja';
    case XMustache = 'text/x-mustache';
    case XHandlebars = 'text/x-handlebars-template';
    case XEjs = 'text/x-ejs';
    case XPug = 'text/x-pug';
    case XSlim = 'text/x-slim';
    case XHaml = 'text/x-haml';
    case XJade = 'text/x-jade';
    case XSmartySrc = 'text/x-smarty-src';
    
    // Web
    case VCard = 'text/vcard';
    case VndWapWml = 'text/vnd.wap.wml';
    case VndWapWmlscript = 'text/vnd.wap.wmlscript';
    case VndSunJ2meAppDescriptor = 'text/vnd.sun.j2me.app-descriptor';
    case VndFlyText = 'text/vnd.fly';
    case VndFmiFlexstor = 'text/vnd.fmi.flexstor';
    case VndGraphviz = 'text/vnd.graphviz';
    case VndIn3d3dml = 'text/vnd.in3d.3dml';
    case VndIn3dSpot = 'text/vnd.in3d.spot';
    case VndIptc = 'text/vnd.iptc.nitf';
    case VndIptcAnpa = 'text/vnd.IPTC.NITF';
    case VndLatexZ = 'text/vnd.latex-z';
    case VndMotorola = 'text/vnd.motorola.reflex';
    case VndMsMediapackage = 'text/vnd.ms-mediapackage';
    case VndNet2phone = 'text/vnd.net2phone.commcenter.command';
    case VndRadisysMsml = 'text/vnd.radisys.msml-basic-layout';
    case VndSi = 'text/vnd.si.uricatalogue';
    case VndWapSi = 'text/vnd.wap.si';
    case VndWapSl = 'text/vnd.wap.sl';
    
    // Other formats
    case Calendar = 'text/calendar';
    case XVcalendar = 'text/x-vcalendar';
    case CacheManifest = 'text/cache-manifest';
    case EventStream = 'text/event-stream';
    case N3 = 'text/n3';
    case Turtle = 'text/turtle';
    case Provenance = 'text/provenance-notation';
    case Shex = 'text/shex';
    case Spdx = 'text/spdx';
    case Strings = 'text/strings';
    case Urilist = 'text/uri-list';
    case Vtt = 'text/vtt';
    case Wgsl = 'text/wgsl';
    case XNfo = 'text/x-nfo';
    case XSfv = 'text/x-sfv';
    case XUuencode = 'text/x-uuencode';
    case XComponent = 'text/x-component';
    case XSetext = 'text/x-setext';
    case XDiff = 'text/x-diff';
    case XPatch = 'text/x-patch';
    case XLog = 'text/x-log';
    case XChangelog = 'text/x-changelog';
    case XAuthors = 'text/x-authors';
    case XTodo = 'text/x-todo';
    case XReadme = 'text/x-readme';
    case XLicense = 'text/x-license';
    case XContributing = 'text/x-contributing';

    /**
     * @return list<string>
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }

    /**
     * Check if given MIME type is a text type
     */
    public static function isText(string $mimeType): bool
    {
        return self::tryFrom($mimeType) !== null 
            || str_starts_with($mimeType, 'text/');
    }

    /**
     * @return list<self>
     */
    public static function programming(): array
    {
        return [
            self::Php,
            self::Python,
            self::Ruby,
            self::Java,
            self::CSource,
            self::CppSource,
            self::CSharp,
            self::Go,
            self::Rust,
            self::Swift,
            self::Kotlin,
            self::TypeScript,
            self::Javascript,
        ];
    }

    /**
     * @return list<self>
     */
    public static function web(): array
    {
        return [
            self::Html,
            self::Css,
            self::Javascript,
            self::TypeScript,
            self::Jsx,
            self::Tsx,
        ];
    }

    /**
     * @return list<self>
     */
    public static function data(): array
    {
        return [
            self::Csv,
            self::Tsv,
            self::Xml,
            self::XYaml,
            self::XToml,
            self::XSql,
        ];
    }

    /**
     * @return list<self>
     */
    public static function markup(): array
    {
        return [
            self::Markdown,
            self::Html,
            self::Asciidoc,
            self::Rst,
            self::Textile,
            self::Org,
        ];
    }
}
