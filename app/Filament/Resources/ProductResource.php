<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ProductResource\Pages;
use App\Models\Product;
use Filament\Forms\Components\RichEditor;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\SpatieMediaLibraryFileUpload;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Forms\Set;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\SpatieMediaLibraryImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Illuminate\Support\Str;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rocket-launch';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                TextInput::make('name')
                ->label(__('Názov'))
                ->maxLength(50)
                ->live()
                ->afterStateUpdated(function (Set $set, $state) {
                    $set('slug', Str::slug($state));
                })
                ->unique(Product::class, 'name', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                    return $rule->where('deleted_at', null);
                })
                ->required(),

                TextInput::make('slug')
                    ->label(__('Slug'))
                    ->disabled()
                    ->unique(Product::class, 'slug', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('deleted_at', null);
                    }),

                TextInput::make('SKU')
                    ->label(__('Číslo produktu'))
                    ->unique(Product::class, 'SKU', ignoreRecord: true, modifyRuleUsing: function ($rule) {
                        return $rule->where('deleted_at', null);
                    })
                    ->required(),

                TextInput::make('price')
                    ->label(__('Cena (€)'))
                    ->numeric()
                    ->inputMode('decimal')
                    ->minValue(0)
                    ->step(0.01)
                    ->required(),

                Toggle::make('active')
                    ->label(__('Aktívny'))
                    ->required(),

                Select::make('categories')
                    ->label(__('Kategórie'))
                    ->relationship('categories', 'name', fn ($query) => $query->where('active', true))
                    ->preload()
                    ->multiple()
                    ->searchable(),

                RichEditor::make('description')
                    ->label(__('Popis'))
                    ->columnSpan('full'),

                SpatieMediaLibraryFileUpload::make('image')
                    ->label(__('Nahrať obrázok'))
                    ->required()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                SpatieMediaLibraryImageColumn::make('image')
                    ->label(__('Obrázok')),

                TextColumn::make('name')
                    ->label(__('Názov'))
                    ->sortable(),

                TextColumn::make('SKU')
                    ->label(__('SKU'))
                    ->sortable(),

                TextColumn::make('price')
                    ->label(__('Cena'))
                    ->money('EUR', locale: config('app.locale'))
                    ->sortable(),

                TextColumn::make('categories_count')
                    ->label(__('Počet kategórií'))
                    ->counts('categories'),

                ToggleColumn::make('active')
                    ->label(__('Aktívny'))
                    ->sortable(),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make()
                    ->action(function (Product $record) {
                        $record->categories()->detach();
                        $record->delete();
                    }),
                Tables\Actions\ForceDeleteAction::make(),
                Tables\Actions\RestoreAction::make()
                    ->before(function (Product $record, $action) {
                        $exists = Product::query()
                            ->where('name', $record->name)
                            ->where('slug', $record->slug)
                            ->whereNull('deleted_at')
                            ->exists();

                        if ($exists) {
                            Notification::make()
                                ->title(__('Obnova zlyhala'))
                                ->body(__('Produkt s rovnakým názvom alebo slug-om už existuje.'))
                                ->danger()
                                ->send();

                            $action->cancel();
                        }
                    }),
            ])
            ->recordUrl(function (Product $record): string {
                return self::getUrl('view', ['record' => $record]);
            });
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'view' => Pages\ViewProduct::route('/{record}'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
